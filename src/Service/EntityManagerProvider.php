<?php

namespace App\Service;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\ORMSetup;

class EntityManagerProvider
{
    protected static EntityManager $entityManager;

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

    protected static function getConfig(): Configuration
    {
        return ORMSetup::createAttributeMetadataConfiguration(
            paths: [__ROOT__ . '/src/Entity'],
            isDevMode: true,
        );
    }

    protected static function getConnection(): array
    {
        return [
            'driver' => 'pdo_sqlite',
            'path' => __ROOT__ . '/' . $_ENV['DB_PATH'],
        ];
    }
}
