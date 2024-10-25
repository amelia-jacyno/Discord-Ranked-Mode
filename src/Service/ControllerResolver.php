<?php

namespace App\Service;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ControllerResolver extends \Symfony\Component\HttpKernel\Controller\ControllerResolver
{
    public function __construct(protected ContainerInterface $container, ?LoggerInterface $logger = null)
    {
        parent::__construct($logger);
    }

    protected function instantiateController(string $class): object
    {
        return $this->container->get($class);
    }
}