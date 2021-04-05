<?php

namespace Local\Bundles\StaticPageMakerBundle\Services;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class TemplateController
 * @package Local\Bundles\StaticPageMakerBundle\Services
 *
 * @since 21.10.2020
 *
 * @see https://github.com/symfony/symfony/blob/5.x/src/Symfony/Bundle/FrameworkBundle/Controller/TemplateController.php
 * Из-за ограничений версии Symfony 4.4 приходится выносить класс локально.
 */
class TemplateController
{
    /**
     * @var Environment|null $twig Twig.
     */
    private $twig;

    /**
     * TemplateController constructor.
     *
     * @param Environment|null $twig Twig.
     */
    public function __construct(Environment $twig = null)
    {
        $this->twig = $twig;
    }

    /**
     * Renders a template.
     *
     * @param string       $template  The template name.
     * @param integer|null $maxAge    Max age for client caching.
     * @param integer|null $sharedAge Max age for shared (proxy) caching.
     * @param boolean|null $private   Whether or not caching should apply for client caches only.
     * @param array        $context   The context (arguments) of the template.
     *
     * @return Response
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function templateAction(
        string $template,
        int $maxAge = null,
        int $sharedAge = null,
        bool $private = null,
        array $context = []
    ): Response {
        if (null === $this->twig) {
            throw new \LogicException('You can not use the TemplateController if the Twig Bundle is not available.');
        }

        $response = new Response($this->twig->render($template, $context));

        if ($maxAge) {
            $response->setMaxAge($maxAge);
        }

        if (null !== $sharedAge) {
            $response->setSharedMaxAge($sharedAge);
        }

        if ($private) {
            $response->setPrivate();
        } elseif (false === $private || (null === $private && (null !== $maxAge || null !== $sharedAge))) {
            $response->setPublic();
        }

        return $response;
    }

    /**
     * Renders a template.
     *
     * @param string       $template  The template name.
     * @param integer|null $maxAge    Max age for client caching.
     * @param integer|null $sharedAge Max age for shared (proxy) caching.
     * @param boolean|null $private   Whether or not caching should apply for client caches only.
     * @param array        $context   The context (arguments) of the template.
     *
     * @return Response
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(
        string $template,
        int $maxAge = null,
        int $sharedAge = null,
        bool $private = null,
        array $context = []
    ): Response {
        return $this->templateAction($template, $maxAge, $sharedAge, $private, $context);
    }
}