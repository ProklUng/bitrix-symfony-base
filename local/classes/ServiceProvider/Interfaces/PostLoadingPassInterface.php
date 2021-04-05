<?php

namespace Local\ServiceProvider\Interfaces;

use Symfony\Component\DependencyInjection\Container;

/**
 * Interface PostLoadingPass
 * То, что применяется к контейнеру после загрузки и инициализации.
 * Автозагрузка и тому подобное.
 * @package Local\ServiceProvider\Interfaces
 *
 * @since 28.09.2020
 */
interface PostLoadingPassInterface
{
    /**
     * То, что запускается после загрузки контейнера.
     *
     * @param Container $containerBuilder Контейнер.
     *
     * @return boolean
     */
    public function action(Container $containerBuilder) : bool;
}
