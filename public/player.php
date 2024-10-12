<?php

use App\Repository\PlayerRepository;
use App\Service\Doctrine\EntityManagerProvider;
use App\Entity;
use Carbon\Carbon;

require_once '../bootstrap.php';

$entityManager = EntityManagerProvider::getEntityManager();
$playerRepository = new PlayerRepository($entityManager);

$playerId = $_GET['id'] ?? null;

if ($playerId === null) {
    header('Location: /');
    exit;
}

/** @var Entity\Player $player */
$player = $playerRepository->find($playerId);

if ($player === null) {
    header('Location: /');
    exit;
}

$playerSnapshots = $player->getSnapshots();
$playerSnapshots->filter(function (Entity\PlayerSnapshot $snapshot) {
    return $snapshot->getCreatedAt()->isAfter(Carbon::now()->subDays(7));
});

$snapshotDays = [];
foreach ($playerSnapshots as $snapshot)
{
    $snapshotDays[$snapshot->getCreatedAt()->format('Y-m-d')] = $snapshot;
}

$xpData = [];
$chartDay = Carbon::now()->subDays(7);
$previousXp = null;
while ($chartDay->isBefore(Carbon::now()))
{
    $snapshot = $snapshotDays[$chartDay->format('Y-m-d')] ?? null;
    if (null === $snapshot)
    {
        $xpData[] = [
            'date' => $chartDay->format('Y-m-d'),
            'xp' => null,
        ];
        $chartDay->addDay();

        continue;
    }

    if (null === $previousXp)
    {
        $previousXp = $snapshot->getXp();

        continue;
    }

    $xpData[] = [
        'date' => $snapshot->getCreatedAt()->format('Y-m-d'),
        'xp' => $snapshot->getXp() - $previousXp,
    ];
    $previousXp = $snapshot->getXp();

    $chartDay->addDay();
}

echo $twig->render('player.html.twig', [
    'player' => $player,
    'xpData' => $xpData,
]);