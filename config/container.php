<?php

$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
    Doctrine\DBAL\Connection::class => function () {
        return Doctrine\DBAL\DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'host' => 'mysql',
            'dbname' => $_ENV['DB_DATABASE'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
        ]);
    },
    Doctrine\ORM\Configuration::class => function () {
        return Doctrine\ORM\ORMSetup::createAttributeMetadataConfiguration(
            paths: [__ROOT__ . '/src/Entity'],
            isDevMode: 'dev' === $_ENV['APP_ENV'],
        );
    },
    Twig\Environment::class => function () {
        $twigLoader = new Twig\Loader\FilesystemLoader(__ROOT__ . '/src/View');
        $twigCache = 'prod' === $_ENV['APP_ENV'] ? __ROOT__ . '/cache/twig' : false;

        return new Twig\Environment($twigLoader, [
            'cache' => $twigCache,
        ]);
    },
    'App\Controller\*' => DI\autowire()
        ->method('setContainer', DI\get(Psr\Container\ContainerInterface::class)),
    Psr\Container\ContainerInterface::class => DI\get(DI\Container::class),
    Doctrine\ORM\EntityManagerInterface::class => DI\autowire(Doctrine\ORM\EntityManager::class),
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
