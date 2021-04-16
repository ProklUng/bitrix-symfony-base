<?php

namespace Local\Services\Utils\Migrations;

use CModule;
use Exception;

/**
 * Trait ModuleTrait
 * @package Local\Services\Utils\Migrations
 */
trait ModuleTrait
{
    /**
     * @param string $name
     *
     * @return array
     *
     * @throws Exception
     */
    public function installModule(string $name): array
    {
        $return = [];
        if (!($module = CModule::CreateModuleObject($name))) {
            throw new Exception("Module {$name} not found");
        } elseif ($module->IsInstalled()) {
            throw new Exception("Module {$name} already installed");
        } else {
            $module->DoInstall();
            $return[] = "Module {$name} installed";
        }

        return $return;
    }

    /**
     * @param string $name
     *
     * @return array
     *
     * @throws Exception
     */
    public function uninstallModule($name)
    {
        $return = [];
        if (!($module = CModule::CreateModuleObject($name))) {
            throw new Exception("Module {$name} not found");
        } elseif (!$module->IsInstalled()) {
            throw new Exception("Module {$name} already uninstalled");
        } else {
            $module->DoUninstall();
            $return[] = "Module {$name} uninstalled";
        }

        return $return;
    }
}
