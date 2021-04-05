<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\PropertiesProcessor;

use Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction\IblockPropertyTypeInterface;
use Local\Bundles\BitrixCustomPropertiesBundle\Services\IblockPropertyType\Abstraction\IblockPropertyTypeNativeInterface;
use RuntimeException;

/**
 * Class CustomIblockPropertiesProcessor
 * Инициализатор кастомных свойств инфоблока.
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\PropertiesProcessor
 *
 * @since 10.02.2021
 */
class CustomIblockPropertiesProcessor
{
    /**
     * @var array $processors Сервисы, помеченные тэгом bitrix.uf.property.type.
     */
    private $processors = [];

    /**
     * CustomUfPropertiesProcessor constructor.
     *
     * @param mixed ...$processors Сервисы, помеченные тэгом bitrix.uf.property.type.
     */
    public function __construct(... $processors)
    {
        $result = [];
        foreach ($processors as $processor) {
            $iterator = $processor->getIterator();
            $result[] = iterator_to_array($iterator);
        }

        $this->processors = array_merge($this->processors, ...$result);
    }

    /**
     * Регистрация событий для создания пользовательских свойств.
     *
     * @return void
     */
    public function register() : void
    {
        /** @var IblockPropertyTypeInterface $processor */
        foreach ($this->processors as $processor) {
            $interfaces = class_implements($processor);
            if (!in_array(IblockPropertyTypeInterface::class, $interfaces, true)
                &&
                !in_array(IblockPropertyTypeNativeInterface::class, $interfaces, true)
            ) {
                throw new RuntimeException(
                    sprintf(
                        'Custom property type error. Class %s not implement IblockPropertyTypeInterface',
                        get_class($processor)
                    )
                );
            }
            $processor->init();
        }
    }
}
