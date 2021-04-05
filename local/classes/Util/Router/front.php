<?php
use Local\Util\Router\Framework;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing;
use Symfony\Component\HttpKernel;

/**
 * Front file for app
 *
 * Grabs request and sets up routing to output response
 * Uses Symfony httpfoundation and routing components
 */

// Create request object
$obRequest = Request::createFromGlobals();
// Маршруты
$routes = include __DIR__.'/routes.php';

// Setup urlmatcher & controller resolver
$obContext = new Routing\RequestContext();
$obMatcher = new Routing\Matcher\UrlMatcher($routes, $obContext);
$resolver = new HttpKernel\Controller\ControllerResolver();

// Setup dispatcher and add route listener
$obDispatcher = new EventDispatcher();
$obDispatcher->addSubscriber(new HttpKernel\EventListener\RouterListener($obMatcher, new RequestStack()));
// Add string response listener
$obDispatcher->addSubscriber(new Local\Util\Router\StringResponseListener());

// Add custom exception listener to return response w/ error msg/status
$listener = new Symfony\Component\HttpKernel\EventListener\ErrorListener(
    '\Local\Util\Router\ErrorController::exceptionAction');
$obDispatcher->addSubscriber($listener);

// Make sure response is in correct charset
$obDispatcher->addSubscriber(new HttpKernel\EventListener\ResponseListener('UTF-8'));

// Setup framework kernel
$obFramework = new Framework($obDispatcher, $resolver);
// Handle response
try {
    $obResponse = $obFramework->handle($obRequest);
} catch (Exception $e) {
    return;
}

// Handle if no route match found
if ($obResponse->getStatusCode() == 404) {
    // If no route found do nothing and let continue
    return;
}

// Send the response to the browser and exit app
$obResponse->send();

exit;
