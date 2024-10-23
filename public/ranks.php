<?php

require_once '../bootstrap.php';

use App\DTO;
use App\Repository\PlayerRepository;
use App\Service\Doctrine\EntityManagerProvider;
use App\Service\PlayerRanksResolver;

$entityManager = EntityManagerProvider::getEntityManager();
$playerRepository = new PlayerRepository($entityManager);
$players = $playerRepository->getPlayersWithAMonthOfSnapshots();

$playerRankInfos = PlayerRanksResolver::resolvePlayerRanks($players);
/** @var array<string, array<DTO\PlayerRankInfo>> $ranks */
$ranks = [];
foreach ($playerRankInfos as $playerRankInfo) {
    $ranks[$playerRankInfo->rank->getName()][] = $playerRankInfo;
}

echo $twig->render('ranks.html.twig', [
    'leaderboard' => $ranks,
]);
