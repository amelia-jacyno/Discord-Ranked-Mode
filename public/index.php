<?php

use App\Service\Kernel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\EventListener\RouterListener;

$container = require_once '../config/bootstrap.php';

$eventDispatcher = $container->get(EventDispatcherInterface::class);
$eventDispatcher->addSubscriber($container->get(RouterListener::class));

$controllerResolver = $container->get(ControllerResolverInterface::class);
$argumentResolver = $container->get(ArgumentResolverInterface::class);
$requestStack = $container->get(RequestStack::class);

$kernel = new Kernel(
    $eventDispatcher,
    $controllerResolver,
    $requestStack,
    $argumentResolver
);

$request = $container->get(Request::class);
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
