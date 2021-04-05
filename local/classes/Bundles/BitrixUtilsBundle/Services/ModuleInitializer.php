<?php

namespace Local\Bundles\BitrixUtilsBundle\Services;

use Bitrix\Main\ModuleManager;
use CModule;
use RuntimeException;

/**
 * Class ModuleInitializer
 * @package Local\Bundles\BitrixUtilsBundle\Services
 *
 * @since 11.03.2021
 */
class ModuleInitializer
{
    /**
     * @var array $modules Модули к инициализации.
     */
    private $modules;

    /**
     * ModuleInitializer constructor.
     *
     * @param array $modules Модули к инициализации.
     */
    public function __construct(array $modules = [])
    {
        $this->modules = $modules;
        define('MODULE_INIT_CLI', true); // Признак - запускаем не из админки.
    }

    /**
     * Инициализация запрошенных модулей.
     *
     * @return void
     */
    public function init() : void
    {
        foreach ($this->modules as $module) {
            $this->install($module);
        }
    }

    /**
     * Инсталляция модуля.
     *
     * @param string $moduleId ID модуля.
     *
     * @return boolean
     */
    public function install(string $moduleId): bool
    {
        if (ModuleManager::isModuleInstalled($moduleId)) {
            return true;
        }

        ob_start();

        $objModuleInitializer = $this->loadModuleClass($moduleId);
        if (!is_object($objModuleInitializer)) {
            return false;
        }

        $objModuleInitializer->DoInstall();
        ob_clean();

        return true;
    }

    /**
     * Удалить модуль.
     *
     * @param string $moduleId ID модуля.
     *
     * @return boolean
     */
    public function delete(string $moduleId) : bool
    {
        if (!ModuleManager::isModuleInstalled($moduleId)) {
            return true;
        }

        ob_start();

        $objModuleInitializer = $this->loadModuleClass($moduleId);
        if (!is_object($objModuleInitializer)) {
            return false;
        }

        $objModuleInitializer->DoUninstall();

        ob_end_clean();

        return true;
    }

    /**
     * @param array $modules Модули.
     *
     * @return ModuleInitializer
     */
    public function setModules(array $modules): ModuleInitializer
    {
        $this->modules = $modules;

        return $this;
    }

    /**
     * Инициализировать класс модуля.
     *
     * @param string $moduleId ID модуля.
     *
     * @return false|mixed
     * @throws RuntimeException
     */
    private function loadModuleClass(string $moduleId)
    {
        $modulePath = getLocalPath('modules/' . $moduleId . '/install/index.php');
        $langPath = getLocalPath('modules/' . $moduleId . '/lang');

        if ($modulePath === false) {
            throw new RuntimeException(
                'Модуль .' . $moduleId . ' не найден.'
            );
        }

        /** @noinspection PhpIncludeInspection */
        require_once $_SERVER['DOCUMENT_ROOT'] . $modulePath;

        $classModule = str_replace('.', '_', $moduleId);

        if (!class_exists($classModule)
            ||
            in_array(CModule::class, class_parents($classModule), true) === false
        ) {
            return false;
        }

        // Языковый файл модулей по умолчанию.
        include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/lang/ru/admin/partner_modules.php');
        if ($langPath) {
            include($_SERVER['DOCUMENT_ROOT'] . $langPath . '/ru/install/install.php');
        }

        return new $classModule;
    }
}
