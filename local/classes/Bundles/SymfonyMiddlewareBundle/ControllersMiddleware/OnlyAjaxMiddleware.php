<?php

namespace Local\Bundles\SymfonyMiddlewareBundle\ControllersMiddleware;

use Local\Bundles\SymfonyMiddlewareBundle\MiddlewareInterface;
use Local\Bundles\SymfonyMiddlewareBundle\ControllersMiddleware\Exceptions\NotAllowedTypeRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OnlyAjaxMiddleware
 * Only AJAX call allowed.
 * @package Local\Services\ControllerMiddleware
 *
 * @since 19.11.2020
 */
class OnlyAjaxMiddleware implements MiddlewareInterface
{
    /**
     * @inheritDoc
     *
     * @throws NotAllowedTypeRequestException
     */
    public function handle(Request $request): ?Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotAllowedTypeRequestException(
                'Only AJAX call allowed.'
            );
        }

        return null;
    }
}
