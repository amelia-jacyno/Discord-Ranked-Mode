<?php

use Psr\Container\ContainerInterface;

return [
    'doctrine' => [
        'connection' => [
            'driver' => 'pdo_mysql',
            'host' => 'mysql',
            'dbname' => $_ENV['DB_DATABASE'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
        ],
        'orm' => [
            'paths' => [__ROOT__ . '/src/Entity'],
            'isDevMode' => 'dev' === $_ENV['APP_ENV'],
            'proxyDir' => __ROOT__ . '/cache/doctrine/proxies',
        ],
    ],
    Doctrine\ORM\EntityManagerInterface::class => DI\autowire(Doctrine\ORM\EntityManager::class),
    Doctrine\DBAL\Connection::class => function (ContainerInterface $c) {
        return Doctrine\DBAL\DriverManager::getConnection(
            $c->get('doctrine')['connection'],
        );
    },
    Doctrine\ORM\Configuration::class => function (ContainerInterface $c) {
        return Doctrine\ORM\ORMSetup::createAttributeMetadataConfiguration(
            paths: $c->get('doctrine')['orm']['paths'],
            isDevMode: $c->get('doctrine')['orm']['isDevMode'],
            proxyDir: $c->get('doctrine')['orm']['proxyDir'],
        );
    },
];
