<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Service\Utils;

use Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions\WrongCsrfException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class CsrfRequestHandler
 * @package Local\SymfonyTools\Framework\Utils
 *
 * @since 05.09.2020
 * @since 04.12.2020 Параметры контейнера пробрасываются снаружи.
 * @since 03.02.2021 Превращение в сервис.
 */
class CsrfRequestHandler
{
    /**
     * @var CsrfTokenManagerInterface $csrfTokenManager Контейнер.
     */
    private $csrfTokenManager;

    /**
     * @var ParameterBagInterface $parameterBag Параметры контейнера.
     */
    private $parameterBag;

    /**
     * CsrfRequestHandler constructor.
     *
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param ParameterBagInterface     $parameterBag     Параметры контейнера.
     */
    public function __construct(
        CsrfTokenManagerInterface $csrfTokenManager,
        ParameterBagInterface $parameterBag
    ) {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->parameterBag = $parameterBag;
    }

    /**
     * Проверить токен из заголовков Request.
     *
     * @param Request $request
     *
     * @return boolean
     *
     * @throws WrongCsrfException Ошибки проверки токена.
     */
    public function validateCsrfToken(Request $request) : bool
    {
        if ($this->parameterBag->get('csrf_protection')) {
            $token = $request->headers->get('x-csrf');

            $bValidToken = $this->csrfTokenManager->isTokenValid(
                new CsrfToken('app', $token)
            );

            if (!$bValidToken) {
                throw new WrongCsrfException('Security error: Invalid CSRF token.');
            }
        }

        return true;
    }
}
