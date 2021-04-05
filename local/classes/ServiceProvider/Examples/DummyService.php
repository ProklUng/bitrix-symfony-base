<?php

namespace Local\ServiceProvider\Examples;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class DummyService
 * @package Local\ServiceProvider\Examples
 * Пример ContainerAwareInterface сервиса.
 *
 * @since 28.09.2020
 */
class DummyService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function doSomethingAwesome()
    {
        $service = $this->container->get('example.service');
        // do awesome stuff
    }
}
