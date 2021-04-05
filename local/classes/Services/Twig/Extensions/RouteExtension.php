<?php

namespace Local\Services\Twig\Extensions;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig_ExtensionInterface;

/**
 * Class RouteExtension
 * @package Local\Services\Twig\Extensions
 *
 * @since 22.10.2020
 */
class RouteExtension extends AbstractExtension implements Twig_ExtensionInterface
{
    /**
     * @var RouteCollection $routeCollection Коллекция роутов.
     */
    protected $routeCollection;

    /**
     * @var ParameterBag $parameterBag Параметры контейнера.
     */
    protected $parameterBag;

    public function __construct(
        RouteCollection $routeCollection,
        ParameterBag $parameterBag
    ) {
        $this->routeCollection = $routeCollection;
        $this->parameterBag = $parameterBag;

        $this->routeCollection->remove(['index', 'remove_trailing_slash', 'not-found']);
    }


    /**
     * Return extension name
     *
     * @return string
     */
    public function getName()
    {
        return 'route_url_extension';
    }

    /**
     * {@inheritdoc}
     */
    /**
     * Twig functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('url', [$this, 'url']),
            new TwigFunction('absolute_url', [$this, 'absoluteUrl']),
            new TwigFunction('path', [$this, 'path']),
        ];
    }

    /**
     * Путь по роуту и его параметрам.
     *
     * @param string $route            ID роута.
     * @param array  $route_parameters Параметры.
     *
     * @return string
     */
    public function path(string $route, array $route_parameters = []) : string
    {
        $routeParams = $this->routeCollection->get($route);

        if (!$routeParams) {
            return '';
        }

        $urlGenerator = new UrlGenerator(
            $this->routeCollection,
            new RequestContext()
        );

        try {
            return $urlGenerator->generate($route, $route_parameters);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Абсолютный (со схемой и хостом) путь.
     *
     * @param string $relativePath Относительный путь.
     *
     * @return string
     */
    public function absoluteUrl(string $relativePath) : string
    {
        if (!$relativePath) {
            return '';
        }

        $schema = $this->parameterBag->get('kernel.schema');
        $host = $this->parameterBag->get('kernel.http.host');

        return $schema . $host . $relativePath;
    }

    /**
     * Абсолютный (со схемой и хостом) путь по роуту и его параметрам.
     *
     * @param string $route            ID роута.
     * @param array  $route_parameters Параметры.
     *
     * @return string
     */
    public function url(string $route, array $route_parameters = []) : string
    {
        $relativePath = $this->path($route, $route_parameters);
        if (!$relativePath) {
            return '';
        }

        return $this->absoluteUrl($relativePath);
    }
}
