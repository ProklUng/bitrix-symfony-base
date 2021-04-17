<?php

namespace Local\Services\Bitrix\Modules;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use function CopyDirFiles;

/**
 * Trait ModuleUtilsTrait
 * @package Local\Services\Bitrix\Modules
 *
 * @since 14.04.2021
 * @since 17.04.2021 Копирование компонентов и файлов при установке. При инсталляции модуля в первую
 * очередь ищется sql файл и только, если он не найден, приступает к сущности.
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
        $result = $this->installDbBatch();

        // Если не null, то уже нашли и пусканули создание таблицы из файла.
        if ($result !== null) {
            return true;
        }

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
        $result = $this->uninstallDbBatch();
        // Если не null, то уже нашли и пусканули создание таблицы из файла.
        if ($result !== null) {
            return true;
        }

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
     * @return void
     */
    public function installFiles(): void
    {
        // Components path mask: MODULE_PATH/install/components/MODULE_NAME.COMPONENT_NAME
        // Where MODULE_NAME in module '[vendor].[module]' is '[module]'
        // @see - https://github.com/detrenasama/bitrix-module-core/blob/master/skel/vendor.modulename/src/Core/Installer.php
        $components = $this->GetModuleDir().'/install/components';
        if (Directory::isDirectoryExists($components)) {
            CopyDirFiles($components, Application::getDocumentRoot() . "/bitrix/components/{$this->getVendor()}/", true, true);
        }

        // Files will be copied into /bitrix/admin/MODULE_ID/files/
        $files = $this->GetModuleDir().'/install/files';
        if (Directory::isDirectoryExists($files)) {
            CopyDirFiles($files, Application::getDocumentRoot() . "/bitrix/admin/{$this->MODULE_ID}/files/", true, true);
        }

        // Если не указаны пути, то пытается по умолчанию рекурсивно копирнуть файлы из /install/admin
        if (count($this->INSTALL_PATHS) === 0) {
            $srcPath = $this->getModuleDir() . '/install/admin';
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
        // Компоненты.
        $components = \glob(Application::getDocumentRoot() . "/bitrix/components/{$this->getVendor()}/{$this->getModuleCode()}.*/");
        foreach ($components as $dir) {
            Directory::deleteDirectory($dir);
        }

        // Файлы.
        $files = Application::getDocumentRoot() . "/bitrix/admin/{$this->MODULE_ID}/files/";
        if (Directory::isDirectoryExists($files)) {
            Directory::deleteDirectory(Application::getDocumentRoot() . "/bitrix/admin/{$this->MODULE_ID}/files/");
        }

        foreach ($this->INSTALL_PATHS as $to) {
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

    /**
     * Директория, где лежит модуль.
     *
     * @return string
     */
    private function getModuleDir() : string
    {
        return $_SERVER['DOCUMENT_ROOT']. '/local/modules/' . $this->MODULE_ID;
    }

    /**
     * Вендор модуля.
     *
     * @return string
     */
    private function getVendor() : string
    {
        return (string) \substr($this->MODULE_ID, 0, \strpos($this->MODULE_ID, '.'));
    }

    /**
     * @return string
     */
    private function getModuleCode() : string
    {
        return (string) \substr($this->MODULE_ID, \strpos($this->MODULE_ID, '.')+1);
    }

    /**
     * Создание таблицы из sql файла.
     *
     * @return null|boolean Когда нет таких файлов - null, иначе результат операции.
     * @throws ArgumentNullException | ArgumentOutOfRangeException Когда что-то пошло не так.
     */
    private function installDbBatch() : ?bool
    {
        global $APPLICATION, $DB;
        $dbBatchFile = $_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$this->MODULE_ID.'/install/batch/db/'
            .strtolower($DB->type).'/install.sql';

        if (!is_file($dbBatchFile)) {
            return null;
        }

        $errors = $DB->RunSQLBatch($dbBatchFile);

        if (is_array($errors)) {
            $APPLICATION->ThrowException(implode(' ', $errors));

            return false;
        }

        return true;
    }

    /**
     * Удаление таблицы посредством sql файла.
     *
     * @return null|boolean Когда нет таких файлов - null, иначе результат операции.
     * @throws ArgumentNullException | ArgumentOutOfRangeException  Когда что-то пошло не так.
     */
    private function unInstallDbBatch() : ?bool
    {
        global $APPLICATION, $DB;

        if (Option::get($this->MODULE_ID, 'UNINSTALL_SAVE_SETTINGS', 0)) {
            return null;
        }

        $dbBatchFile = $_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$this->MODULE_ID
            .'/install/batch/db/'.strtolower($DB->type).'/uninstall.sql';

        if (!is_file($dbBatchFile)) {
            return null;
        }

        $errors = $DB->RunSQLBatch($dbBatchFile);

        if (is_array($errors)) {
            $APPLICATION->ThrowException(implode(' ', $errors));

            return false;
        }

        return true;
    }
}