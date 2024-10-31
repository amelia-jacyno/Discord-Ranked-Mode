<?php

namespace App\Service;

use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Routing\Route;

class AttributeClassLoader extends \Symfony\Component\Routing\Loader\AttributeClassLoader
{
    #[NoReturn]
    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, object $annot): void
    {
        $route->addDefaults([
            '_controller' => $class->getName() . '::' . $method->getName(),
        ]);
    }
}
