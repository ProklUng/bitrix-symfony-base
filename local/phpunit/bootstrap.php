<?php
$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(__DIR__));

define("LANGUAGE_ID", "pa");
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("LOG_FILENAME", 'php://stderr');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Альтернативный способ вывода ошибок типа "DB query error.":
$GLOBALS["DB"]->debug = true;

global $DB;
$app = \Bitrix\Main\Application::getInstance();
$con = $app->getConnection();
$DB->db_Conn = $con->getResource();

// "authorizing" as admin
$_SESSION["SESS_AUTH"]["USER_ID"] = 1;
$_SESSION['PHPUNIT_RUNNING'] = true;
