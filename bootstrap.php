<?php

const __ROOT__ = __DIR__;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

Doctrine\DBAL\Types\Type::overrideType('datetime_immutable', \Carbon\Doctrine\CarbonImmutableType::class);
Doctrine\DBAL\Types\Type::overrideType('datetime', \Carbon\Doctrine\CarbonType::class);

date_default_timezone_set('UTC');