<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Entity;
use App\Repository\PlayerRepository;
use App\Service\Doctrine\EntityManagerProvider;
use App\Service\LeaderboardProvider\LeaderboardProviderResolver;
use Carbon\Carbon;

// Check for 2nd argument to be 'force' to force update
$force = false;
if ($argc > 2) {
    if ('force' !== $argv[2]) {
        echo 'Invalid argument.' . PHP_EOL;
        exit;
    }

    $force = true;
}

$entityManager = EntityManagerProvider::getEntityManager();
/** @var PlayerRepository $playerRepository */
$playerRepository = $entityManager->getRepository(Entity\Player::class);
if (!$force) {
    $lastUpdate = $playerRepository->getLastPlayerSnapshotUpdate();
    if (isset($lastUpdate) && $lastUpdate->diffInHours(Carbon::now()) < 12) {
        echo 'Players already updated less than 12 hours ago.' . PHP_EOL;
        exit;
    }
}

$externalPlayers = LeaderboardProviderResolver::resolveProvider($_ENV['LEADERBOARD_PROVIDER'] ?? 'mee6')::fetchPlayers();

foreach ($externalPlayers as $playerData) {
    $player = $playerRepository->findOneBy(['externalId' => $playerData->id]);

    if (null === $player) {
        $player = (new Entity\Player())
            ->setExternalId($playerData->id);
        $entityManager->persist($player);
    }

    $player
        ->setUsername($playerData->username)
        ->setAvatar($playerData->avatarUrl);

    $snapshot = (new Entity\PlayerSnapshot())
        ->setLevel($playerData->level)
        ->setXp($playerData->xp)
        ->setMessageCount($playerData->messageCount ?? 0)
        ->setPlayer($player);
    $player->addSnapshot($snapshot);
}
$entityManager->flush();
echo 'Players updated successfully.' . PHP_EOL;
