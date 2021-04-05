<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle\Services\PropertiesProcessor;

/**
 * Class CustomUfPropertiesProcessor
 * Инициализатор кастомных пользовательских (UF) свойств.
 * @package Local\Bundles\BitrixCustomPropertiesBundle\Services\PropertiesProcessor
 *
 * @since 09.02.2021
 */
class CustomUfPropertiesProcessor
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
        foreach ($this->processors as $processor) {
            /** @psalm-suppress UndefinedFunction */
            AddEventHandler(
                'main',
                'OnUserTypeBuildList',
                [$processor, 'GetUserTypeDescription']
            );
        }
    }
}