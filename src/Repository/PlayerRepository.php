<?php

namespace App\Repository;

use App\DTO;
use App\Entity;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

final class PlayerRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, new ClassMetadata(Entity\Player::class));
    }

    /**
     * @return DTO\PlayerWithSnapshotData[]
     */
    public function getPlayersWithSnapshotData(Entity\Guild $guild): array
    {
        $sql = "
            SELECT
                p.id,
                p.username,
                p.avatar,
                p.external_id,
                ps_oldest.xp AS oldest_snapshot_xp,
                ps_oldest.level AS oldest_snapshot_level,
                ps_oldest.created_at AS oldest_snapshot_date,
                ps_newest.xp AS newest_snapshot_xp,
                ps_newest.level AS newest_snapshot_level,
                ps_newest.created_at AS newest_snapshot_date
            FROM 
                players p
            INNER JOIN 
                player_snapshots ps_oldest ON p.id = ps_oldest.player_id AND ps_oldest.created_at = (
                    SELECT 
                        MIN(created_at)
                    FROM 
                        player_snapshots ps
                    WHERE 
                        ps.player_id = p.id
                        AND ps.guild_id = :guildId
                        AND ps.created_at >= :oldestSnapshotDate
                )
            INNER JOIN
                player_snapshots ps_newest ON p.id = ps_newest.player_id AND ps_newest.created_at = (
                    SELECT 
                        MAX(created_at)
                    FROM 
                        player_snapshots ps
                    WHERE 
                        ps.player_id = p.id
                        AND ps.guild_id = :guildId
                        AND ps.created_at <= :newestSnapshotDate
                )
        ";
        try
        {
            $result = $this->getEntityManager()->getConnection()->executeQuery($sql, [
                'guildId' => $guild->getId(),
                'oldestSnapshotDate' => Carbon::now()->subDays(30)->format('Y-m-d H:i:s'),
                'newestSnapshotDate' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            $playersWithSnapshotData = [];
            foreach ($result->iterateAssociative() as $row)
            {
                $playersWithSnapshotData[] = new DTO\PlayerWithSnapshotData(
                    $row['id'],
                    $row['username'],
                    $row['avatar'],
                    $row['external_id'],
                    $row['oldest_snapshot_xp'],
                    $row['oldest_snapshot_level'],
                    new Carbon($row['oldest_snapshot_date']),
                    $row['newest_snapshot_xp'],
                    $row['newest_snapshot_level'],
                    new Carbon($row['newest_snapshot_date'])
                );
            }

            return $playersWithSnapshotData;
        }
        catch (\Exception $e)
        {
            throw new \RuntimeException('Failed to get player snapshot data.', 0, $e);
        }
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getLastPlayerSnapshotUpdate(Entity\Guild $guild): ?CarbonInterface
    {
        $qb = $this->createQueryBuilder('p');
        $result = $qb
            ->select('MAX(s.createdAt)')
            ->leftJoin('p.snapshots', 's')
            ->where('s.guild = :guild')
            ->setParameter('guild', $guild)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? new Carbon($result) : null;
    }
}
