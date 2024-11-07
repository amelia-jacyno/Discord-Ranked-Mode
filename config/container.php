<?php

$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
    'env' => $_ENV['APP_ENV'] ?? 'prod',
    'debug' => fn (DI\Container $c) => 'dev' === $c->get('env'),
    Psr\Container\ContainerInterface::class => DI\get(DI\Container::class),
    'App\Controller\*' => DI\autowire()
        ->method('setContainer', DI\get(Psr\Container\ContainerInterface::class)),
    Symfony\Component\EventDispatcher\EventDispatcherInterface::class => DI\autowire(Symfony\Component\EventDispatcher\EventDispatcher::class),
    Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface::class => DI\autowire(Symfony\Component\HttpKernel\Controller\ArgumentResolver::class),
    Symfony\Component\HttpKernel\Controller\ControllerResolverInterface::class => DI\autowire(App\Service\ControllerResolver::class),
]);

$packages = Symfony\Component\Finder\Finder::create()
    ->files()
    ->in(__ROOT__ . '/config/packages')
    ->name('*.php')
    ->sortByName();

foreach ($packages as $package) {
    $builder->addDefinitions(require $package->getRealPath());
}

$container = $builder->build();

return $container;
