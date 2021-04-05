<?php

namespace Local\ServiceProvider\Utils;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteChecker
 * @package Local\ServiceProvider\Utils
 *
 * @since 11.10.2020
 */
class RouteChecker
{
    /**
     * @var RouteCollection $routeCollection Роуты.
     */
    private $routeCollection;

    /**
     * RouteChecker constructor.
     *
     * @param RouteCollection $routeCollection Роуты.
     */
    public function __construct(
        RouteCollection $routeCollection
    ) {

        $this->routeCollection = $routeCollection;
    }

    /**
     * Проверка роута на существование.
     *
     * @param string $uri URL.
     *
     * @return boolean
     */
    public function isLiveRoute(string $uri): bool
    {
        // Setup urlmatcher & controller resolver
        $context = new RequestContext();
        $context->fromRequest(Request::createFromGlobals());

        $matcher = new UrlMatcher($this->routeCollection, $context);

        try {
            $matcher->match($uri);
            return true;
        } catch (ResourceNotFoundException $e) {
            return false;
        } catch (MethodNotAllowedException $e) {
            return true;
        }
    }
}