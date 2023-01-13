<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Repository\PlayerRepository;
use App\Service\EntityManagerProvider;
use App\Service\PlayerRanksResolver;

$entityManager = EntityManagerProvider::getEntityManager();
$playerRepository = new PlayerRepository($entityManager);
$players = $playerRepository->getPlayersWithAMonthOfSnapshots();

$ranks = PlayerRanksResolver::resolvePlayerRanks($players);

foreach ($ranks as $rank => $players) {
	echo $rank . ' (' . count($players) . ')' .  PHP_EOL;
	foreach ($players as $player) {
		echo '  ' . $player->getUsername() . PHP_EOL;
	}
	echo PHP_EOL;
}