<?php

use App\Controller\LeaderboardController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('home', new Route('/', [
    '_controller' => fn (Request $request) => new RedirectResponse('/leaderboard'),
]));
$routes->add('leaderboard', new Route('/leaderboard', [
    '_controller' => [LeaderboardController::class, 'leaderboard'],
]));
$routes->add('ranks', new Route('/ranks', [
    '_controller' => [LeaderboardController::class, 'ranks'],
]));
$routes->add('player', new Route('/player/{playerId}', [
    '_controller' => [LeaderboardController::class, 'player'],
]));

return $routes;