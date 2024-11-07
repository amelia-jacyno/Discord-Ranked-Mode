<?php

$path = __DIR__ . '/../../src/Controller';
$loader = new Symfony\Component\Routing\Loader\AttributeDirectoryLoader(
    new Symfony\Component\Config\FileLocator($path),
    new App\Service\AttributeClassLoader()
);
$router = new Symfony\Component\Routing\Router($loader, $path);

return [
    'router' => DI\get(Symfony\Component\Routing\RouterInterface::class),
    'request' => DI\get(Symfony\Component\HttpFoundation\Request::class),
    Symfony\Component\Routing\RouterInterface::class => $router,
    Symfony\Component\HttpFoundation\Request::class => function () {
        return Symfony\Component\HttpFoundation\Request::createFromGlobals();
    },
    Symfony\Component\HttpKernel\EventListener\RouterListener::class => DI\create()
        ->constructor(
            DI\get(Symfony\Component\Routing\RouterInterface::class),
            DI\get(Symfony\Component\HttpFoundation\RequestStack::class),
            debug: DI\get('debug'),
        ),
];
