<?php

namespace Local\Bundles\SymfonyMiddlewareBundle\ControllersMiddleware;

use Local\Bundles\SymfonyMiddlewareBundle\MiddlewareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExampleMiddleware
 * @package Local\Services\ControllerMiddleware
 *
 * @since 19.11.2020
 */
class ExampleMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): ?Response
    {
        if ($request->query->get('test')) {
            return new Response('OK2');
        }
        return null;
    }
}
