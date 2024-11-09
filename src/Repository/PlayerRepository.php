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
                ps.xp AS snapshot_xp,
                ps.level AS snapshot_level,
                ps.created_at AS snapshot_date
            FROM 
                players p
            INNER JOIN 
                player_snapshots ps
                ON p.id = ps.player_id
            WHERE
                ps.guild_id = :guildId
                AND ps.created_at >= :oldestSnapshotDate
            ORDER BY
                p.id,
                ps.created_at
        ";
        try
        {
            $result = $this->getEntityManager()->getConnection()->executeQuery($sql, [
                'guildId' => $guild->getId(),
                'oldestSnapshotDate' => Carbon::now()->subDays(30)->format('Y-m-d H:i:s'),
            ]);

            $playerData = null;
            $oldestSnapshotData = null;
            $newestSnapshotData = null;
            $playersWithSnapshotData = [];
            foreach ($result->iterateAssociative() as $row)
            {
                if ($playerData === null || $playerData['id'] !== $row['id'])
                {
                    if ($playerData !== null && $oldestSnapshotData !== null && $newestSnapshotData !== null)
                    {
                        $playersWithSnapshotData[] = new DTO\PlayerWithSnapshotData(
                            $playerData['id'],
                            $playerData['username'],
                            $playerData['avatar'],
                            $playerData['external_id'],
                            $oldestSnapshotData['xp'],
                            $oldestSnapshotData['level'],
                            new Carbon($oldestSnapshotData['date']),
                            $newestSnapshotData['xp'],
                            $newestSnapshotData['level'],
                            new Carbon($newestSnapshotData['date'])
                        );
                    }

                    $playerData = $row;
                    $oldestSnapshotData = [
                        'xp' => $row['snapshot_xp'],
                        'level' => $row['snapshot_level'],
                        'date' => $row['snapshot_date'],
                    ];
                    $newestSnapshotData = null;

                    continue;
                }

                $newestSnapshotData = [
                    'xp' => $row['snapshot_xp'],
                    'level' => $row['snapshot_level'],
                    'date' => $row['snapshot_date'],
                ];
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
