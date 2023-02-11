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
			'driver' => 'pdo_sqlite',
			'path' => __ROOT__ . '/' . $_ENV['DB_PATH'],
		]);
	}
}