<?php

namespace Local\Bundles\SymfonyBladeBundle\Services\BladeProcessors;

use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Local\Bundles\SymfonyBladeBundle\Services\BladeCompilerBitrix;
use Local\Bundles\SymfonyBladeBundle\Services\Filters\BladeFiltersCompiler;

/**
 * Class BladeBitrix
 * @package Local\Bundles\SymfonyBladeBundle\Services\BladeProcessors
 */
class BladeBitrix extends BladeBase
{
    /**
     * Register the Blade engine implementation for Bitrix.
     *
     * @param EngineResolver $resolver Resolver.
     *
     * @return void
     */
    public function registerBladeEngine(EngineResolver $resolver) : void
    {
        $me = $this;
        $app = $this->container;

        $this->container->singleton('blade.compiler.bitrix', function ($app) use ($me) {
            $cache = $me->cachePath;

            return new BladeCompilerBitrix($app['files'], $cache);
        });

        $app['blade.compiler.bitrix']->extend(function ($view) use ($app) {
            return $app[BladeFiltersCompiler::class]->compile($view);
        });

        $resolver->register('blade', function () use ($app) {
            return new CompilerEngine($app['blade.compiler.bitrix']);
        });
    }
}
