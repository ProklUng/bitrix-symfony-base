<?php

if (!function_exists('htmlspecialcharsbx')) {
	function htmlspecialcharsbx($string, $flags=ENT_COMPAT) {
		return htmlspecialchars($string, $flags, (defined('BX_UTF')? 'UTF-8' : 'ISO-8859-1'));
	}
}

CModule::AddAutoloadClasses(
	'asd.iblock',
	array(
		'CASDiblock' => 'classes/general/iblock_interface.php',
		'CASDiblockInterface' => 'classes/general/iblock_interface.php',
		'CASDiblockAction' => 'classes/general/iblock_action.php',
		'CASDiblockTools' => 'classes/general/iblock_tools.php',
		'CASDIblockElementTools' => 'classes/general/iblock_tools.php',
		'CASDiblockPropCheckbox' => 'classes/general/iblock_prop_checkbox.php',
		'CASDiblockPropCheckboxNum' => 'classes/general/iblock_prop_checkbox_num.php',
		'CASDiblockPropPalette' => 'classes/general/iblock_prop_palette.php',
		'CASDiblockPropSection' => 'classes/general/iblock_prop_section.php',
		'CASDIblockRights' => 'classes/general/iblock_rights.php',
		'CASDiblockVersion' => 'classes/general/iblock_version.php',
		'CASDModuleVersion' => 'classes/general/module_version.php'
	)
);

$arJSAsdIBlockConfig = [
	'asd_iblock' => [
		'js' => '/local/modules/asd.iblock/install/js/asd.iblock/script.js',
		'css' => '/local/modules/asd.iblock/install/panel/asd.iblock/interface.css',
		'rel' => ['jquery'],
    ],
	'asd_element_list' => [
		'js' => '/bitrix/js/asd.iblock/admin/elementlist.js',
		'css' => '/local/modules/asd.iblock/install/panel/asd.iblock/interface.css',
		'rel' => ['core']
    ],
	'asd_palette' => [
		'js' => '/local/modules/asd.iblock/install/js/asd.iblock/jpicker/jpicker-1.1.6.min.js',
		'css' => '/local/modules/asd.iblock/install/js/asd.iblock/jpicker/css/jPicker-1.1.6.min.css',
		'rel' => ['jquery'],
    ],
];

foreach ($arJSAsdIBlockConfig as $ext => $arExt) {
	CJSCore::RegisterExt($ext, $arExt);
}
