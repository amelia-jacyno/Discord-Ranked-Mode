<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use App\Controller\LeaderboardController;
use HaydenPierce\ClassFinder\ClassFinder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

require_once '../bootstrap.php';

$routes = new RouteCollection();
$controllers = ClassFinder::getClassesInNamespace('App\Controller');
foreach ($controllers as $controller)
{
    $class = new ReflectionClass($controller);
    $methods = $class->getMethods();

    foreach ($methods as $method)
    {
        $routeAttribute = $method->getAttributes(\Symfony\Component\Routing\Attribute\Route::class);
        foreach ($routeAttribute as $attribute)
        {
            /** @var \Symfony\Component\Routing\Attribute\Route $route */
            $route = $attribute->newInstance();
            $routes->add($route->getName(), new Route($route->getPath(), [
                '_controller' => [LeaderboardController::class, $method->getName()],
            ]));
        }
    }
}

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
