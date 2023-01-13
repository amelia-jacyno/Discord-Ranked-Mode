<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Entity;
use App\Repository\PlayerRepository;
use App\Service\EntityManagerProvider;
use App\Service\PlayerFetcher;
use Carbon\Carbon;

$entityManager = EntityManagerProvider::getEntityManager();
/** @var PlayerRepository $playerRepository */
$playerRepository = $entityManager->getRepository(Entity\Player::class);
$lastUpdate = $playerRepository->getLastPlayerSnapshotUpdate();
if (isset($lastUpdate) && $lastUpdate->diffInHours(Carbon::now()) < 12)
{
	echo 'Players already updated less than 12 hours ago.' . PHP_EOL;
	exit;
}
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
echo 'Players updated successfully.' . PHP_EOL;