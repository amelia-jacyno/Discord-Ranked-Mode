<?php

namespace App\Repository;

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
     * @return Entity\Player[]
     */
    public function getPlayersWithAMonthOfSnapshots(): array
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->addSelect('s')
            ->leftJoin('p.snapshots', 's', 'WITH', 's.createdAt >= :monthAgo')
            ->orderBy('s.createdAt', 'DESC')
            ->setParameter('monthAgo', Carbon::now()->subMonth())
            ->getQuery()
            ->getResult();
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
