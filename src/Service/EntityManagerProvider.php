<?php

namespace App\Service;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\ORMSetup;

final class EntityManagerProvider
{
    private static EntityManager $entityManager;

    /**
     * @throws ORMException
     */
    public static function getEntityManager(): EntityManager
    {
        if (!isset(self::$entityManager)) {
            self::$entityManager = EntityManager::create(self::getConnection(), self::getConfig());
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

    private static function getConnection(): array
    {
        return [
            'driver' => 'pdo_sqlite',
            'path' => __ROOT__ . '/' . $_ENV['DB_PATH'],
        ];
    }
}
