<?php

use HaydenPierce\ClassFinder\ClassFinder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

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
                '_controller' => $controller . '::' . $method->getName()
            ]));
        }
    }
}

return $routes;