<?php

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('home', new Route('/', [
    '_controller' => fn (Request $request) => new RedirectResponse('/leaderboard'),
]));
$routes->add('leaderboard', new Route('/leaderboard', [
    '_controller' => fn (Request $request) => render_template($request),
]));
$routes->add('ranks', new Route('/ranks', [
    '_controller' => fn (Request $request) => render_template($request),
]));
$routes->add('player', new Route('/player', [
    '_controller' => fn (Request $request) => render_template($request),
]));

return $routes;