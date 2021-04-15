<?php

namespace Local\Services\Bitrix\Modules;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

/**
 * Trait ModuleUtilsTrait
 * @package Local\Services\Bitrix\Modules
 *
 * @since 14.04.2021
 */
trait ModuleUtilsTrait
{
    /**
     * @var Module $moduleManager
     */
    protected $moduleManager;

    /**
     * @var array $INSTALL_PATHS Пути файлов (или директорий) улетающих в bitrix/admin
     * (или куда укажут) при установке модуля. В случае необходимости должно быть переопределено
     * в классе модуля.
     */
    protected $INSTALL_PATHS = [];

    /**
     * @var string $MODULE_FULL_NAME
     */
    protected $MODULE_VENDOR = '';

    /**
     * @var string $MODULE_FULL_NAME
     */
    protected $MODULE_FULL_NAME = '';

    /**
     * @var string $MODULE_TABLE_ENTITY Класс ORM таблицы, используемой модулем.
     * Если пусто - таблица не используется.
     */
    protected $MODULE_TABLE_ENTITY = '';

    /**
     * Вывод формы админки модуля.
     *
     * @return void
     * @throws ArgumentNullException
     */
    public function showOptionsForm() : void
    {
        $this->moduleManager->showOptionsForm();
    }

    /**
     * Экземпляр менеджера модулей.
     *
     * @return Module
     */
    public function getModuleManager(): Module
    {
        return $this->moduleManager;
    }

    public function doInstall(): void
    {
        ModuleManager::registerModule($this->MODULE_ID);

        $this->installDB();
        $this->installFiles();
        $this->InstallEvents();
    }

    public function doUninstall(): void
    {
        $this->uninstallDB();
        $this->uninstallFiles();
        $this->UnInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installDB()
    {
        // Не задана необходимость генерировать таблицу.
        if ($this->MODULE_TABLE_ENTITY === '') {
            return true;
        }

        if (Loader::includeModule($this->MODULE_ID))
        {
            $tableName = $this->MODULE_TABLE_ENTITY::getTableName();
            if (!Application::getConnection()->isTableExists($tableName)) {
                $this->MODULE_TABLE_ENTITY::getEntity()->createDbTable();
            }

            return true;
        }

        return false;
    }

    public function uninstallDB()
    {
        if ($this->MODULE_TABLE_ENTITY === '') {
            return;
        }

        if (Loader::includeModule($this->MODULE_ID))
        {
            $connection = Application::getInstance()->getConnection();
            try {
                $connection->dropTable($this->MODULE_TABLE_ENTITY::getTableName());
            } catch (\Exception $e) {
                // Ошибки типа таблица не найдена - глушатся.
            }

        }
    }

    public function InstallEvents() {

    }

    public function UnInstallEvents() {

    }

    /**
     * Install module files.
     *
     * In production mode recursively copy all files in directories passed to $this->INSTALL_PATHS
     * In development mode create symlink defined in $this->DEV_LINKS.
     *
     * @return void
     */
    public function installFiles(): void
    {
        // Если не указаны пути, то пытается по умолчанию рекурсивно копирнуть файлы из /install/admin
        if (count($this->INSTALL_PATHS) === 0) {
            $srcPath = $_SERVER['DOCUMENT_ROOT']. '/local/modules/' . $this->MODULE_ID . '/install/admin';
            $destPath = $_SERVER['DOCUMENT_ROOT']. '/bitrix/admin/';
            CopyDirFiles($srcPath, $destPath, true, true);

            return;
        }

        foreach ($this->INSTALL_PATHS as $from => $to) {
            CopyDirFiles($_SERVER['DOCUMENT_ROOT'].$from, $_SERVER['DOCUMENT_ROOT'].$to, true, true);
        }
    }

    /**
     * Remove files and symlinks created by module.
     *
     * @return void
     */
    public function uninstallFiles(): void
    {
        foreach ($this->INSTALL_PATHS as $from => $to) {
            if (is_file($_SERVER['DOCUMENT_ROOT'] . $to)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $to);
                continue;
            }

            if (is_dir($_SERVER['DOCUMENT_ROOT'] . $to)) {
                $this->rrmdir($_SERVER['DOCUMENT_ROOT'] . $to);
            }
        }
    }

    /**
     * Подготовка данных для генерации админки модуля.
     *
     * @return void
     */
    protected function options() :void
    {
        $optionsManager = $this->moduleManager->getOptionsManager();

        $optionsManager->addTabs($this->getSchemaTabsAdmin());
        $optionsManager->addOptions($this->getSchemaOptionsAdmin());
    }

    /**
     * Схема табов настройки опций.
     *
     * @return array
     */
    protected function getSchemaTabsAdmin() : array
    {
        return [];
    }

    /**
     * Схема опций.
     *
     * @return array
     */
    protected function getSchemaOptionsAdmin() : array
    {
        return [];
    }

    /**
     * Рекурсивно удалить папки и файлы в них.
     *
     * @param string $dir Директория.
     */
    private function rrmdir(string $dir) : void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (is_dir($dir.DIRECTORY_SEPARATOR.$object) && !is_link($dir.'/'.$object)) {
                        $this->rrmdir($dir.DIRECTORY_SEPARATOR.$object);
                    } else {
                        unlink($dir.DIRECTORY_SEPARATOR.$object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}
