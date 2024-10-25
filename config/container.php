<?php

$builder = new DI\ContainerBuilder();
$builder->addDefinitions([
    \Doctrine\DBAL\Connection::class => function () {
        return \Doctrine\DBAL\DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'host' => 'mysql',
            'dbname' => $_ENV['DB_DATABASE'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
        ]);
    },
    \Doctrine\ORM\Configuration::class => function () {
        return \Doctrine\ORM\ORMSetup::createAttributeMetadataConfiguration(
            paths: [__ROOT__ . '/src/Entity'],
            isDevMode: $_ENV['APP_ENV'] === 'dev',
        );
    },
    \Doctrine\ORM\EntityManagerInterface::class => DI\autowire(\Doctrine\ORM\EntityManager::class),
    \Twig\Environment::class => function () {
        $twigLoader = new \Twig\Loader\FilesystemLoader(__ROOT__ . '/src/View');
        $twigCache = $_ENV['APP_ENV'] === 'prod' ? __ROOT__ . '/cache/twig' : false;

        return new \Twig\Environment($twigLoader, [
            'cache' => $twigCache,
        ]);
    },
    'App\Controller\*' => DI\autowire()
        ->method('setContainer', DI\get(\Psr\Container\ContainerInterface::class)),
    \Psr\Container\ContainerInterface::class => DI\get(DI\Container::class),
]);

$container = $builder->build();

return $container;