<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

require_once '../bootstrap.php';

function render_template(Request $request): Response
{
    $twigLoader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../src/View');
    $twigCache = $_ENV['APP_ENV'] === 'prod' ? __DIR__ . '/../cache/twig' : false;
    $twig = new \Twig\Environment($twigLoader, [
        'cache' => $twigCache,
    ]);

    extract($request->attributes->all(), EXTR_SKIP);
    ob_start();
    require_once sprintf('/%s/%s.php', __DIR__, $_route);

    return new Response(ob_get_clean());
}

$routes = require_once '../src/routes.php';
$request = Request::createFromGlobals();

$context = new RequestContext();
$context->fromRequest($request);
$matcher = new UrlMatcher($routes, $context);

try {
    $request->attributes->add($matcher->match($request->getPathInfo()));
    $response = call_user_func($request->attributes->get('_controller'), $request);
} catch (ResourceNotFoundException $exception) {
    $response = new Response('Not Found', 404);
} catch (Exception $exception) {
    $response = new Response('An error occurred: ' . $exception->getMessage(), 500);
}

$response->send();
