<?php

namespace App\Service\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

class DBALConnectionProvider
{
    /**
     * @throws Exception
     */
    public static function getConnection(): Connection
    {
        return DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'host' => 'mysql',
            'dbname' => $_ENV['DB_DATABASE'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
        ]);
    }
}
