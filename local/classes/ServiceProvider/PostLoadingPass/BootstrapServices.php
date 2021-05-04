<?php

namespace Local\ServiceProvider\PostLoadingPass;

use Exception;
use Local\ServiceProvider\Interfaces\PostLoadingPassInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Class BootstrapServices
 *
 * Автозагрузка сервисов.
 *
 * @package Local\ServiceProvider\PostLoadingPass
 *
 * @since 28.09.2020
 * @since 04.05.2021 Исключения сервисов автозагрузки больше не глушатся.
 */
class BootstrapServices implements PostLoadingPassInterface
{
    /** @const string VARIABLE_PARAM_BAG Переменная в ParameterBag. */
    private const VARIABLE_PARAM_BAG = '_bootstrap';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function action(Container $containerBuilder) : bool
    {
        try {
            $bootstrapServices = $containerBuilder->getParameter(self::VARIABLE_PARAM_BAG);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        if (count($bootstrapServices) === 0) {
            return false;
        }

        foreach ($bootstrapServices as $service => $value) {
            $containerBuilder->get($service);
        }

        return true;
    }
}
