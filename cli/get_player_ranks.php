<?php

require_once __DIR__ . '/../bootstrap.php';

use App\DTO\PlayerRankInfo;
use App\Repository\PlayerRepository;
use App\Service\EntityManagerProvider;
use App\Service\PlayerRanksResolver;

$entityManager = EntityManagerProvider::getEntityManager();
$playerRepository = new PlayerRepository($entityManager);
$players = $playerRepository->getPlayersWithAMonthOfSnapshots();
$playerRankInfos = PlayerRanksResolver::resolvePlayerRanks($players);
/** @var array<string, array<PlayerRankInfo>> $ranks */
$ranks = [];
foreach ($playerRankInfos as $playerRankInfo) {
	$ranks[$playerRankInfo->rank][] = $playerRankInfo;
}

foreach ($ranks as $rank => $players) {
    echo $rank . ' (' . count($players) . ')' . PHP_EOL;
    foreach ($players as $player) {
        echo '  ' . $player->username . ': ' . round($player->dailyXp, 1) . 'xp' . PHP_EOL;
    }
    echo PHP_EOL;
}
