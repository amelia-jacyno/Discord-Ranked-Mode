<?php
require_once '../bootstrap.php';

use App\Entity\Player;
use App\Repository\PlayerRepository;
use App\Service\Doctrine\EntityManagerProvider;

$entityManager = EntityManagerProvider::getEntityManager();
$playerRepository = new PlayerRepository($entityManager);
$players = $playerRepository->getPlayersWithAMonthOfSnapshots();
usort($players, fn (Player $p1, Player $p2) => $p2->getXp() <=> $p1->getXp());
$players = array_slice($players, 0, 100);

echo $twig->render('leaderboard.html.twig', [
    'players' => $players,
]);