<?php

namespace App\Service\Doctrine;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\MissingMappingDriverImplementation;

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
            self::$entityManager = new EntityManager(
				DBALConnectionProvider::getConnection(),
				ORMConfigurationProvider::getConfiguration());
        }

        return self::$entityManager;
    }
}
