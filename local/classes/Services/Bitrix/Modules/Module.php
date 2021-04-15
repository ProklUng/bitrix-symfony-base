<?php

namespace Local\Services\Bitrix\Modules;

use Bitrix\Main\ArgumentNullException;
use Exception;
use Local\Services\Bitrix\Modules\Exception\MainModuleManagerException;
use LogicException;

/**
 * Class Module
 * @package Local\Services\Bitrix\Modules
 *
 * @since 13.04.2021
 */
class Module
{
    /**
     * @var string $MODULE_ID ID модуля.
     */
    private $MODULE_ID;

    /**
     * @var string $MODULE_VERSION Версия модуля.
     */
    private $MODULE_VERSION;

    /**
     * @var string $MODULE_VERSION_DATE Дата версии модуля.
     */
    private $MODULE_VERSION_DATE;

    /**
     * @var string $ADMIN_FORM_ID ID формы админки модуля.
     */
    private $ADMIN_FORM_ID;

    /**
     * @var Options\ModuleManager $options Менеджер опций модуля.
     */
    public $options;

    /**
     * @var array $moduleInstances Инициализированные экземпляры менеджеров модулей.
     */
    private static $moduleInstances = [];

    /**
     * @param array $options Array of module properties.
     *
     * @throws MainModuleManagerException Когда с параметрами что-то не то.
     */
    public function __construct(array $options = [])
    {
        if (!$options['MODULE_ID']) {
            throw new MainModuleManagerException('MODULE_ID is required');
        }

        $this->MODULE_ID = $options['MODULE_ID'];
        $this->MODULE_VERSION = $options['MODULE_VERSION'];
        $this->MODULE_VERSION_DATE = $options['MODULE_VERSION_DATE'];
        $this->ADMIN_FORM_ID = $options['ADMIN_FORM_ID'];

        $this->options = new Options\ModuleManager($this->MODULE_ID);
    }

    /**
     * Добавить в стэк экземпляр менеджера модулей.
     *
     * @param object $moduleObject Экземпляр менеджера модулей.
     *
     * @return void
     */
    public function addModuleInstance($moduleObject) : void
    {
        static::$moduleInstances[$this->MODULE_ID] = $moduleObject;
    }

    /**
     * Получить экземпляр менеджера модуля по ID.
     *
     * @param string $moduleId ID модуля.
     *
     * @return object
     */
    public static function getModuleInstance(string $moduleId)
    {
        if (array_key_exists($moduleId, static::$moduleInstances)) {
            return static::$moduleInstances[$moduleId];
        }

        throw new LogicException(
            sprintf('Модуль c id %s не найден.', $moduleId)
        );
    }

    /**
     * @return Options\ModuleManager
     */
    public function getOptionsManager(): Options\ModuleManager
    {
        return $this->options;
    }

    /**
     * Get module id.
     *
     * @return string MODULE_ID property
     */
    public function getId(): string
    {
        return $this->MODULE_ID;
    }

    /**
     * Get module version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->MODULE_VERSION;
    }

    /**
     * Set module version.
     *
     * @param string $MODULE_VERSION Версия модуля.
     *
     * @return void
     */
    public function setVersion(string $MODULE_VERSION): void
    {
        $this->MODULE_VERSION = $MODULE_VERSION;
    }

    /**
     * Get module version date.
     *
     * @return string MODULE_VERSION_DATE property
     */
    public function getVersionDate(): string
    {
        return $this->MODULE_VERSION_DATE;
    }

    /**
     * Set module version date.
     *
     * @param string $MODULE_VERSION_DATE Дата модуля.
     *
     * @return void
     */
    public function setVersionDate(string $MODULE_VERSION_DATE): void
    {
        $this->MODULE_VERSION_DATE = $MODULE_VERSION_DATE;
    }

    /**
     * Output admin options form.
     *
     * @return void
     * @throws ArgumentNullException Когда что-то пошло не так.
     */
    public function showOptionsForm(): void
    {
        $form = new Options\ModuleForm($this->options, $this->ADMIN_FORM_ID);
        $form->handleRequest();
        $form->write();
    }
}
