<?php

namespace Local\Services\Bitrix\Modules\Options;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;

/**
 * Class ModuleManager
 * @package Local\Services\Bitrix\Modules\Options
 *
 * @since 13.04.2021
 */
class ModuleManager
{
    /**
     * @var string $moduleId ID модуля.
     */
    private $moduleId;

    /**
     * @var array $optionFields Опции.
     */
    private $optionFields = [];

    /**
     * @var array $optionTabs Табы.
     */
    private $optionTabs = [];

    /**
     * @var array $optionValues Значения опций.
     */
    private $optionValues = [];

    /**
     * ModuleManager constructor.
     *
     * @param string $moduleId Module id.
     */
    public function __construct(string $moduleId)
    {
        $this->moduleId = $moduleId;
    }

    /**
     * Add one option.
     *
     * @param string $id     Option id.
     * @param array  $option Array of option params.
     *
     * @return void
     */
    public function addOption(string $id, array $option): void
    {
        $this->optionFields[$id] = $option;
    }

    /**
     * Add multiple options at once.
     *
     * @param array $options An associative array [string $id => array $optionParams].
     *
     * @return void
     */
    public function addOptions(array $options): void
    {
        foreach ($options as $key => $option) {
            $this->addOption($key, $option);
        }
    }

    /**
     * Add options tab.
     *
     * @param string $id  Tab id.
     * @param array  $tab An array of tab params.
     *
     * @return void
     */
    public function addTab(string $id, array $tab): void
    {
        $this->optionTabs[] = array_merge($tab, ['DIV' => $id]);
    }

    /**
     * Add multiple tabs at once.
     *
     * @param array $tabs Associative array [string $id => array $tabParams].
     *
     * @return void
     */
    public function addTabs(array $tabs): void
    {
        foreach ($tabs as $key => $tab) {
            $this->addTab($key, $tab);
        }
    }

    /**
     * Get module option value.
     *
     * @param string $name Option id.
     *
     * @return mixed option value
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentNullException
     */
    public function get(string $name)
    {
        if (empty($this->optionValues)) {
            $this->loadOptionValues();
        }

        return $this->optionValues[$name];
    }

    /**
     * Get all module options values.
     *
     * @return array
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentNullException
     */
    public function getAll(): array
    {
        if (empty($this->optionValues)) {
            $this->loadOptionValues();
        }

        return $this->optionValues;
    }

    /**
     * Get default options values.
     *
     * @return array
     */
    public function getDefaults(): array
    {
        return array_map(static function ($val) {
            return $val['default'];
        }, $this->optionFields);
    }

    /**
     * Get tabs.
     *
     * @return array
     */
    public function getTabs(): array
    {
        return $this->optionTabs;
    }

    /**
     * Get option.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->optionFields;
    }

    /**
     * Getter for moduleId field.
     *
     * @return string
     */
    public function getModuleId(): string
    {
        return $this->moduleId;
    }

    /**
     * Load module options values fallback to defaults.
     *
     * @return void
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentNullException
     */
    private function loadOptionValues(): void
    {
        $this->optionValues = array_merge(
            Option::getDefaults($this->moduleId),
            Option::getForModule($this->moduleId)
        );
    }
}
