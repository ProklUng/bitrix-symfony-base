<?php

namespace Local\Bundles\SymfonyBladeBundle\Services\BladeProcessors;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Local\Bundles\SymfonyBladeBundle\Services\Filters\BladeFiltersCompiler;
use Local\Bundles\SymfonyBladeBundle\Services\ViewFinder;

/**
 * Class BladeBase
 * @package Local\Bundles\SymfonyBladeBundle\Services\BladeProcessors
 *
 * @since 08.03.2021
 */
class BladeBase
{
    /**
     * Array of view base directories.
     *
     * @var array
     */
    protected $viewPaths;

    /**
     * Local path to blade cache storage.
     *
     * @var string
     */
    protected $cachePath;

    /**
     * Service container instance.
     *
     * @var Container
     */
    protected $container;

    /**
     * View factory instance.
     *
     * @var Factory
     */
    protected $viewFactory;

    /**
     * Constructor.
     *
     * @param array     $viewPaths Пути к шаблонам.
     * @param string    $cachePath Путь к кэшу.
     * @param Container $container Контейнер.
     */
    public function __construct(array $viewPaths, string $cachePath, Container $container)
    {
        $this->viewPaths = $viewPaths;
        $this->cachePath = $cachePath;
        $this->container = $container;

        $this->registerFilesystem();
        $this->registerEvents();
        $this->registerEngineResolver();
        $this->registerViewFinder();
        $this->registerFactory();
    }

    /**
     * Getter for view factory.
     *
     * @return Factory
     */
    public function view() : Factory
    {
        return $this->viewFactory;
    }

    /**
     * Register the view factory.
     *
     * @return void
     */
    public function registerFactory() : void
    {
        $resolver = $this->container['view.engine.resolver'];

        $finder = $this->container['view.finder'];

        $factory = new Factory($resolver, $finder, $this->container['events']);
        $factory->setContainer($this->container);

        $this->viewFactory = $factory;
    }

    /**
     * Register filesystem in container.
     *
     * @return void
     */
    public function registerFilesystem() : void
    {
        $this->container->singleton('files', function () : Filesystem {
            return new Filesystem();
        });
    }

    /**
     * Register events in container.
     *
     * @return void
     */
    public function registerEvents() : void
    {
        $this->container->singleton('events', function () : Dispatcher {
            return new Dispatcher();
        });
    }

    /**
     * Register the engine resolver instance.
     *
     * @return void
     */
    public function registerEngineResolver() : void
    {
        $me = $this;

        $this->container->singleton('view.engine.resolver', function () use ($me) : EngineResolver {
            $resolver = new EngineResolver();

            $me->registerPhpEngine($resolver);
            $me->registerBladeEngine($resolver);

            return $resolver;
        });
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param EngineResolver $resolver Resolver.
     *
     * @return void
     */
    public function registerPhpEngine(EngineResolver $resolver) : void
    {
        $resolver->register('php', function () : PhpEngine {
            return new PhpEngine();
        });
    }

    /**
     * Register the Blade engine implementation.
     *
     * @param EngineResolver $resolver Resolver.
     *
     * @return void
     */
    public function registerBladeEngine(EngineResolver $resolver) : void
    {
        $me = $this;
        $app = $this->container;

        $this->container->singleton('blade.compiler', function (Container $app) use ($me) : BladeCompiler {
            $cache = $me->cachePath;

            return new BladeCompiler($app['files'], $cache);
        });

        $app['blade.compiler']->extend(function (string $view) use ($app) : string {
            return $app[BladeFiltersCompiler::class]->compile($view);
        });

        $resolver->register('blade', function () use ($app) : CompilerEngine {
            return new CompilerEngine($app['blade.compiler']);
        });
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder() : void
    {
        $me = $this;
        $this->container->singleton('view.finder', function (Container $app) use ($me) : ViewFinder {
            $paths = $me->viewPaths;

            return new ViewFinder($app['files'], $paths);
        });
    }
}
