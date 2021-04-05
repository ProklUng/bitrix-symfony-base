<?php

global $MESS;

$pathInstall = str_replace('\\', '/', __FILE__);
$pathInstall = substr($pathInstall, 0, strlen($pathInstall) - strlen('/index.php'));
$resultInclude = IncludeModuleLangFile($pathInstall.'/install.php');

include($pathInstall.'/version.php');

if (class_exists('asd_iblock')) {
    return;
}

/**
 * Class asd_iblock
 */
class asd_iblock extends CModule
{

    /**
     * @var string $MODULE_ID ID модуля.
     */
    public $MODULE_ID = 'asd.iblock';

    /**
     * @var string $MODULE_VERSION
     */
    public $MODULE_VERSION;

    /**
     * @var string $MODULE_VERSION_DATE
     */
    public $MODULE_VERSION_DATE;

    /**
     * @var string $MODULE_NAME
     */
    public $MODULE_NAME;

    /**
     * @var string $MODULE_DESCRIPTION
     */
    public $MODULE_DESCRIPTION;

    /**
     * @var string $PARTNER_NAME
     */
    public $PARTNER_NAME;

    /**
     * @var string $PARTNER_URI
     */
    public $PARTNER_URI;

    /**
     * @var string $MODULE_GROUP_RIGHTS
     */
    public $MODULE_GROUP_RIGHTS = 'N';

    /**
     * @var string $NEED_MAIN_VERSION
     */
    public $NEED_MAIN_VERSION = '';

    /**
     * @var array $NEED_MODULES
     */
    public $NEED_MODULES = [];

    /**
     * asd_iblock constructor.
     */
    public function __construct()
    {
        $arModuleVersion = [];

        $path = str_replace('\\', '/', __FILE__);
        $path = substr($path, 0, strlen($path) - strlen('/index.php'));
        include $path.'/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->PARTNER_NAME = GetMessage('ASD_PARTNER_NAME');
        $this->PARTNER_URI = 'http://www.d-it.ru/solutions/modules/';

        $this->MODULE_NAME = GetMessage('ASD_IBLOCK_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('ASD_IBLOCK_MODULE_DESCRIPTION');
    }

    /**
     * @inerhitDoc
     */
    public function DoInstall(): void
    {
        if (is_array($this->NEED_MODULES) && !empty($this->NEED_MODULES)) {
            foreach ($this->NEED_MODULES as $module) {
                if (!IsModuleInstalled($module)) {
                    $this->showForm('ERROR', GetMessage('ASD_NEED_MODULES', ['#MODULE#' => $module]));
                }
            }
        }

        if (strlen($this->NEED_MAIN_VERSION) <= 0 || version_compare(SM_VERSION, $this->NEED_MAIN_VERSION) >= 0) {
            $this->InstallEvents();

            RegisterModule('asd.iblock');
            $this->showForm('OK', GetMessage('MOD_INST_OK'));

            return;
        }

        $this->showForm('ERROR', GetMessage('ASD_NEED_RIGHT_VER', ['#NEED#' => $this->NEED_MAIN_VERSION]));
    }

    /**
     * @inerhitDoc
     */
    public function InstallEvents(): void
    {
        if (strlen($this->NEED_MAIN_VERSION) <= 0 || version_compare(SM_VERSION, $this->NEED_MAIN_VERSION) >= 0) {
            RegisterModuleDependences('main', 'OnAdminListDisplay', 'asd.iblock', 'CASDiblockInterface',
                'OnAdminListDisplayHandler');

            RegisterModuleDependences(
                'main',
                'OnAdminSubListDisplay',
                'asd.iblock',
                'CASDiblockInterface',
                'OnAdminSubListDisplayHandler'
            );
            RegisterModuleDependences('main', 'OnBeforeProlog', 'asd.iblock', 'CASDiblockAction',
                'OnBeforePrologHandler');
            RegisterModuleDependences('main', 'OnAdminContextMenuShow', 'asd.iblock', 'CASDiblockInterface',
                'OnAdminContextMenuShowHandler');
            RegisterModuleDependences('main', 'OnAdminTabControlBegin', 'asd.iblock', 'CASDiblockInterface',
                'OnAdminTabControlBeginHandler');
            RegisterModuleDependences('iblock', 'OnAfterIBlockUpdate', 'asd.iblock', 'CASDiblockAction',
                'OnAfterIBlockUpdateHandler');
            RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'asd.iblock', 'CASDiblockPropCheckbox',
                'GetUserTypeDescription');
            RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'asd.iblock', 'CASDiblockPropCheckboxNum',
                'GetUserTypeDescription');
            RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'asd.iblock', 'CASDiblockPropPalette',
                'GetUserTypeDescription');
            RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'asd.iblock', 'CASDiblockPropSection',
                'GetUserTypeDescription');
        }
    }

    /**
     * @inerhitDoc
     */
    public function UnInstallEvents(): void
    {
        UnRegisterModuleDependences('main', 'OnAdminListDisplay', 'asd.iblock', 'CASDiblockInterface',
            'OnAdminListDisplayHandler');
        UnRegisterModuleDependences('main', 'OnAdminSubListDisplay', 'asd.iblock', 'CASDiblockInterface',
            'OnAdminSubListDisplayHandler');
        UnRegisterModuleDependences('main', 'OnBeforeProlog', 'asd.iblock', 'CASDiblockAction',
            'OnBeforePrologHandler');
        UnRegisterModuleDependences('main', 'OnAdminContextMenuShow', 'asd.iblock', 'CASDiblockInterface',
            'OnAdminContextMenuShowHandler');
        UnRegisterModuleDependences('main', 'OnAdminTabControlBegin', 'asd.iblock', 'CASDiblockInterface',
            'OnAdminTabControlBeginHandler');
        UnRegisterModuleDependences('iblock', 'OnAfterIBlockUpdate', 'asd.iblock', 'CASDiblockAction',
            'OnAfterIBlockUpdateHandler');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'asd.iblock', 'CASDiblockPropCheckbox',
            'GetUserTypeDescription');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'asd.iblock', 'CASDiblockPropCheckboxNum',
            'GetUserTypeDescription');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'asd.iblock', 'CASDiblockPropPalette',
            'GetUserTypeDescription');
        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'asd.iblock', 'CASDiblockPropSection',
            'GetUserTypeDescription');
    }

    /**
     * @inerhitDoc
     */
    public function DoUninstall()
    {
        UnRegisterModule('asd.iblock');
        $this->UnInstallEvents();

        $this->showForm('OK', GetMessage('MOD_UNINST_OK'));
    }

    /**
     * @param mixed  $type
     * @param string $message
     * @param string $buttonName
     */
    private function showForm($type, string $message, string $buttonName = ''): void
    {
        if (defined('MODULE_INIT_CLI')) {
          return;
        }

        global $APPLICATION;

        $keys = array_keys($GLOBALS);
        for ($i = 0, $intCount = count($keys); $i < $intCount; $i++) {
            if ($keys[$i] !== 'i' && $keys[$i] !== 'GLOBALS' && $keys[$i] !== 'strTitle' && $keys[$i] !== 'filepath') {
                global ${$keys[$i]};
            }
        }

        $PathInstall = str_replace('\\', '/', __FILE__);
        $PathInstall = substr($PathInstall, 0, strlen($PathInstall) - strlen('/index.php'));
        IncludeModuleLangFile($PathInstall.'/install.php');

        $APPLICATION->SetTitle(GetMessage('ASD_IBLOCK_MODULE_NAME'));
        include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
        $cAdminMessage = new CAdminMessage('');
        $cAdminMessage->ShowMessage(['MESSAGE' => $message, 'TYPE' => $type]);
        ?>
      <form action="<?= $APPLICATION->GetCurPage() ?>" method="get">
        <p>
          <input type="hidden" name="lang" value="<? echo LANGUAGE_ID; ?>"/>
          <input type="submit" value="<?= strlen($buttonName) ? $buttonName : GetMessage('MOD_BACK') ?>"/>
        </p>
      </form>
        <?php
       include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
    }
}
