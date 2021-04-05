<?php

namespace Local\Util\Autowiring;

/**
 * Class WireProperties
 * Автовайринг свойств.
 * @package Local\Util\Autowiring
 */
class WireProperties
{
    /**
     * Обработать объект.
     *
     * @param mixed $object Объект.
     *
     * @return mixed
     */
    public function process($object)
    {
        $propertiesProcessor = new PropertiesWiring(
            $object,
            container()
        );

        return $propertiesProcessor->wire();
    }

    /**
     * Экран смерти.
     *
     * @param string $errorMessage
     */
    protected function errorScreen(string $errorMessage) : void
    {
        die($errorMessage);
    }
}
