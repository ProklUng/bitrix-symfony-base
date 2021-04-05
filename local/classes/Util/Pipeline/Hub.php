<?php

namespace Local\Util\Pipeline;

use Closure;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class Hub
 * @package Local\Util\Pipeline
 *
 * Hub, вытащенный из Laravel и заточенный под контейнер Symfony.
 *
 * @since 01.10.2020
 */
class Hub
{
    /**
     * The container implementation.
     *
     * @var Container|null
     */
    protected $container;

    /**
     * All of the available pipelines.
     *
     * @var array
     */
    protected $pipelines = [];

    /**
     * Create a new Hub instance.
     *
     * @param Container|null $container
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container;
    }

    /**
     * Define the default named pipeline.
     *
     * @param Closure $callback
     *
     * @return void
     */
    public function defaults(Closure $callback)
    {
        return $this->pipeline('default', $callback);
    }

    /**
     * Define a new named pipeline.
     *
     * @param string $name
     * @param Closure $callback
     */
    public function pipeline($name, Closure $callback)
    {
        $this->pipelines[$name] = $callback;
    }

    /**
     * Send an object through one of the available pipelines.
     *
     * @param mixed       $object
     * @param string|null $pipeline
     *
     * @return mixed
     */
    public function pipe($object, $pipeline = null)
    {
        $pipeline = $pipeline ?: 'default';

        return call_user_func(
            $this->pipelines[$pipeline],
            new Pipeline($this->container),
            $object
        );
    }
}
