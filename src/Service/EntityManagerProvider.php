<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\MissingMappingDriverImplementation;
use Doctrine\ORM\ORMSetup;

final class EntityManagerProvider
{
    private static EntityManager $entityManager;

	/**
	 * @throws MissingMappingDriverImplementation
	 * @throws Exception
	 */
	public static function getEntityManager(): EntityManagerInterface
    {
        if (!isset(self::$entityManager)) {
            self::$entityManager = new EntityManager(self::getConnection(), self::getConfig());
        }

        return self::$entityManager;
    }

    private static function getConfig(): Configuration
    {
        return ORMSetup::createAttributeMetadataConfiguration(
            paths: [__ROOT__ . '/src/Entity'],
            isDevMode: true,
        );
    }

	/**
	 * @throws Exception
	 */
	private static function getConnection(): Connection
    {
        return DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'path' => __ROOT__ . '/' . $_ENV['DB_PATH'],
        ], self::getConfig());
    }
}
