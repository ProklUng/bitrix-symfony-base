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
 */
class BootstrapServices implements PostLoadingPassInterface
{
    /** @const string VARIABLE_PARAM_BAG Переменная в ParameterBag. */
    private const VARIABLE_PARAM_BAG = '_bootstrap';

    /**
     * @inheritDoc
     */
    public function action(Container $containerBuilder) : bool
    {
        $result = false;

        try {
            $bootstrapServices = $containerBuilder->getParameter(self::VARIABLE_PARAM_BAG);
        } catch (InvalidArgumentException $e) {
            return $result;
        }

        if (empty($bootstrapServices)) {
            return $result;
        }

        foreach ($bootstrapServices as $service => $value) {
            try {
                $containerBuilder->get($service);
                $result = true;
            } catch (Exception $e) {
                continue;
            }
        }

        return $result;
    }
}
