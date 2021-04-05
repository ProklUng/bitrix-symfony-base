<?php

namespace Local\SymfonyTools\Framework\Utils;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Local\SymfonyTools\Framework\Exceptions\WrongCsrfException;

/**
 * Class CsrfRequestHandler
 * @package Local\SymfonyTools\Framework\Utils
 *
 * @since 05.09.2020
 */
class CsrfRequestHandler
{
    /**
     * @var Request $request Запрос.
     */
    private $request;

    /**
     * @var ContainerInterface $container Контейнер.
     */
    private $container;

    /**
     * CsrfRequestHandler constructor.
     *
     * @param Request            $request   Запрос.
     * @param ContainerInterface $container Контейнер.
     */
    public function __construct(
        Request $request,
        ContainerInterface $container
    ) {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * Проверить токен из заголовков Request.
     *
     * @return boolean
     *
     * @throws WrongCsrfException Невалидный CSRF токен.
     */
    public function validateCsrfToken() : bool
    {
        if ($this->container->getParameter('csrf_protection')) {
            $token = $this->request->headers->get('x-csrf');

            if (!$this->container->has('security.csrf.token_manager')) {
                throw new WrongCsrfException('CSRF protection is not enabled in your application.');
            }

            $bValidToken = $this->container->get('security.csrf.token_manager')->isTokenValid(
                new CsrfToken('app', $token)
            );

            if (!$bValidToken) {
                throw new WrongCsrfException('Security error: Invalid CSRF token.');
            }
        }

        return true;
    }
}
