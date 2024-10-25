<?php

namespace App\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class AbstractController
{
    protected ContainerInterface $container;

    #[Required]
    private function setContainer(ContainerInterface $twig): void
    {
        $this->container = $twig;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function render(string $template, array $context = []): Response
    {
        $twig = $this->container->get(Environment::class);

        try {
            return new Response(
                $twig->render($template, $context),
                Response::HTTP_OK,
                ['content-type' => 'text/html']
            );
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw new \RuntimeException('Failed to render template', 0, $e);
        }
    }
}
