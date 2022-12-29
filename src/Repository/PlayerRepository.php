<?php

namespace App\Repository;

use App\Entity\Player;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

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
}