<?php

require_once '../bootstrap.php';

use App\DTO\ExternalPlayer;
use App\Service\LeaderboardProvider\LeaderboardProviderResolver;

$externalPlayers = LeaderboardProviderResolver::resolveProvider($_ENV['LEADERBOARD_PROVIDER'] ?? 'mee6')::fetchPlayers();
usort($externalPlayers, fn (ExternalPlayer $p1, ExternalPlayer $p2) => $p2->xp <=> $p1->xp);
$externalPlayers = array_slice($externalPlayers, 0, 100);

$hasMessageCounts = false;
foreach ($externalPlayers as $externalPlayer) {
    if (null !== $externalPlayer->messageCount) {
        $hasMessageCounts = true;
        break;
    }
}

echo $twig->render('leaderboard.html.twig', [
    'externalPlayers' => $externalPlayers,
    'hasMessageCounts' => $hasMessageCounts,
]);
