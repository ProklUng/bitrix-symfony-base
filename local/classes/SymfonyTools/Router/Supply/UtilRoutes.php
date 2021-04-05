<?php

namespace Local\SymfonyTools\Router\Supply;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Supplemental
 * Маршруты API Symfony router.
 * @package Local\Router\Supply
 *
 * @since 07.09.2020
 */
class UtilRoutes
{
    /**
     * Handle root and any other routes
     *
     * Set to 404 response and allow app front to send back to wp.
     *
     * @param Request $request
     * @return Response
     */
    //if index return not found
    public function indexRoute(Request $request) : Response
    {
        return new Response('catch-all', 404);
    }

    //if not found return 404
    public function notFound(Request $request) : Response
    {
        return new Response('catch-all', 404);
    }
}
