<?php

namespace App\Controller;

use App\Service\Twig;
use Symfony\Component\HttpFoundation\Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class AbstractController
{
    public static function render(string $template, array $context = []): Response
    {
        try
        {
            return new Response(
                Twig::render($template, $context),
                Response::HTTP_OK,
                ['content-type' => 'text/html']
            );
        }
        catch (LoaderError | RuntimeError | SyntaxError $e)
        {
            throw new \RuntimeException('Failed to render template', 0, $e);
        }
    }
}