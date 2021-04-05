<?php

/*
 * This file is part of CoopTilleulsUrlSignerBundle.
 *
 * (c) Les-Tilleuls.coop <contact@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Local\Bundles\UrlSignedBundle\EventListener;

use Local\Bundles\UrlSignedBundle\UrlSigner\UrlSignerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class ValidateSignedRouteListener
 * @package Local\Bundles\UrlSignedBundle\EventListener
 */
final class ValidateSignedRouteListener
{
    /**
     * @var UrlSignerInterface $urlSigner Проверка URL.
     */
    private $urlSigner;

    /**
     * ValidateSignedRouteListener constructor.
     *
     * @param UrlSignerInterface $urlSigner Проверка URL.
     */
    public function __construct(UrlSignerInterface $urlSigner)
    {
        $this->urlSigner = $urlSigner;
    }

    /**
     * @param RequestEvent $event Событие.
     *
     * @return void
     * @throws AccessDeniedHttpException Доступ запрещен.
     */
    public function validateSignedRoute(RequestEvent $event): void
    {
        $request = $event->getRequest();
        /** @var array{_signed?: bool} $routeParams */
        $routeParams = $request->attributes->get('_route_params');

        if (!$routeParams || !($routeParams['_signed'] ?? false)) {
            return;
        }

        if (!$this->urlSigner->validate($request->getRequestUri())) {
            throw new AccessDeniedHttpException('URL is either missing a valid signature or have a bad signature.');
        }
    }
}
