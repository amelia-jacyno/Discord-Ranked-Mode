<?php

return [
    'twig.paths' => [
        __ROOT__ . '/src/View',
    ],
    'twig.options' => [
        'cache' => 'prod' === $_ENV['APP_ENV'] ? __ROOT__ . '/cache/twig' : false,
    ],
    'twig.loader' => DI\get(Twig\Loader\LoaderInterface::class),
    Twig\Loader\LoaderInterface::class => DI\get(Twig\Loader\FilesystemLoader::class),
    Twig\Loader\FilesystemLoader::class => DI\create()
        ->constructor(DI\get('twig.paths')),
    Twig\Environment::class => DI\create()
        ->constructor(DI\get('twig.loader'), DI\get('twig.options')),
];
