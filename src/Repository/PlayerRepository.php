<?php

namespace App\Repository;

use App\Entity\Player;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class PlayerRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, new ClassMetadata(Player::class));
    }

    /**
     * @return Player[]
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
    public function getLastPlayerSnapshotUpdate(): ?CarbonInterface
    {
        $qb = $this->createQueryBuilder('p');
        $result = $qb
            ->select('MAX(s.createdAt)')
            ->leftJoin('p.snapshots', 's')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? new Carbon($result) : null;
    }
}
