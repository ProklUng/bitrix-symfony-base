<?php

class CASDModuleVersion
{
    /**
     * @var array $moduleVersion
     */
    protected static $moduleVersion = [];

    /**
     * @param $module
     * @return mixed
     */
    public static function getModuleVersion($module)
    {
        if (!isset(self::$moduleVersion[$module])) {
            self::loadModuleVersion($module);
        }

        return self::$moduleVersion[$module];
    }

    /**
     * @param string $module
     * @param string $version
     *
     * @return boolean | integer
     */
    public static function checkMinVersion(string $module, string $version)
    {
        if (!isset(self::$moduleVersion[$module])) {
            self::loadModuleVersion($module);
        }
        if (self::$moduleVersion[$module] === '0.0.0') {
            return false;
        }

        return version_compare(self::$moduleVersion[$module], $version, '>=');
    }

    /**
     * @param string $module
     *
     * @return void
     */
    protected static function loadModuleVersion(string $module): void
    {
        self::$moduleVersion[$module] = '0.0.0';
        $moduleObject = CModule::CreateModuleObject($module);
        if ($moduleObject) {
            self::$moduleVersion[$module] = $moduleObject->MODULE_VERSION;
        }

        unset($moduleObject);
    }
}
