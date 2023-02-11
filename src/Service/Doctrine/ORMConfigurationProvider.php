<?php

namespace App\Service\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\ORMSetup;

final class ORMConfigurationProvider
{
	public static function getConfiguration(): Configuration
	{
		return ORMSetup::createAttributeMetadataConfiguration(
			paths: [__ROOT__ . '/src/Entity'],
			isDevMode: true,
		);
	}
}