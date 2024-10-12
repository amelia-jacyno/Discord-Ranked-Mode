<?php

const __ROOT__ = __DIR__;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$twigLoader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/src/View');

$twigCache = $_ENV['APP_ENV'] === 'prod' ? __DIR__ . '/cache/twig' : false;
$twig = new \Twig\Environment($twigLoader, [
    'cache' => $twigCache,
]);

Doctrine\DBAL\Types\Type::overrideType('datetime_immutable', \Carbon\Doctrine\CarbonImmutableType::class);
Doctrine\DBAL\Types\Type::overrideType('datetime', \Carbon\Doctrine\CarbonType::class);

date_default_timezone_set('UTC');