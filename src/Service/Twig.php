<?php

namespace App\Service;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class Twig
{
    protected static Environment $twig;

    public static function getTwig(): Environment
    {
        if (isset(static::$twig)) {
            return static::$twig;
        }

        $twigLoader = new FilesystemLoader(__DIR__ . '/../View');
        $twigCache = 'prod' === $_ENV['APP_ENV'] ? __DIR__ . '/../../cache/twig' : false;

        static::$twig = new Environment($twigLoader, [
            'cache' => $twigCache,
        ]);

        return static::$twig;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public static function render(string $template, array $context = []): string
    {
        return static::getTwig()
            ->render($template, $context);
    }
}
