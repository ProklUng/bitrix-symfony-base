<?php

require_once __DIR__ . '/vendor/autoload.php';

@set_time_limit(0);

$_SERVER['DOCUMENT_ROOT'] = __DIR__. DIRECTORY_SEPARATOR . 'sites/s1';
$GLOBALS['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'];

define('SITE_CHARSET', 'UTF-8');
define('SITE_ID', 's1');
define("LANGUAGE_ID", 'ru');
define("LANG", 'ru');
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define("LOG_FILENAME", 'php://stderr');
define("BX_NO_ACCELERATOR_RESET", true);
define("STOP_STATISTICS", true);
define("NO_AGENT_STATISTIC", "Y");
define("NO_AGENT_CHECK", true);
defined('PUBLIC_AJAX_MODE') || define('PUBLIC_AJAX_MODE', true);

