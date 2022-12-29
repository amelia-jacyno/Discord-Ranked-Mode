<?php

require_once __DIR__ . '/bootstrap.php';

use App\Entity;
use App\Service\EntityManagerProvider;
use App\Service\PlayerFetcher;

$entityManager = EntityManagerProvider::getEntityManager();
$playerRepository = $entityManager->getRepository(Entity\Player::class);
$playersData = PlayerFetcher::fetchPlayers();
foreach ($playersData as $playerData)
{
	$player = $playerRepository->findOneBy(['externalId' => $playerData['id']]);

	if ($player === null) {
		$player = (new Entity\Player())
			->setExternalId($playerData['id'])
			->setUsername($playerData['username']);
		$entityManager->persist($player);
	}

	$snapshot = (new Entity\PlayerSnapshot())
		->setLevel($playerData['level'])
		->setXp($playerData['xp'])
		->setMessageCount($playerData['message_count'])
		->setPlayer($player);
	$player->addSnapshot($snapshot);
}
$entityManager->flush();