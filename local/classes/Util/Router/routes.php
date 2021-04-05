<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing;
use Symfony\Component\Routing\Route;

/**
 * Create routes for app
 *
 * - Uses Symfony Routing component
 * - Set either a closure or call to controller
 * - Route name maps to template name to render
 */

// Новая коллекция маршрутов.
$routes = new Routing\RouteCollection();

/**
 * Route forwarded to controller with last segment of url added as param
 */
$routes->add(
    'api-instagram',
    new Route(
        '/api/v1/instagram/',
        [
            '_controller' => '\Local\Util\Router\InstagramParserController::action',
            'methods' => 'GET',
            'count_pictures' => 3, // Количество картинок.
            'instagram' => 'savoy_seychelles', // Инстаграм.

        ]
    )
);


/**
 * Handle root and any other routes
 *
 * Set to 404 response and allow app front to send back to Bitrix.
 */

$routes->add(
    'index',
    new Route(
        '/',
        [
            '_controller' => function (Request $request) {
                return $response = new Response('catch-all', 404);
            },
        ],
        [
            'url' => '.',
        ]
    )
);

//if not found return 404
$routes->add(
    'not-found',
    new Route(
        '/{url}',
        [
            '_controller' => function (Request $request) {
                return $response = new Response('catch-all', 404);
            },
        ],
        [
            'url' => '.+',
        ]
    )
);

return $routes;
