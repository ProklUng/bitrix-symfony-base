<?php

namespace Local\Commands\Utils;

use Bitrix\Main\Application;
use Bitrix\Main\DB\ConnectionException;

/**
 * Class LoaderBitrix
 * @package Local\Commands\Utils
 *
 * @since 11.12.2020
 */
class LoaderBitrix
{
    /**
     * Bitrix is unavailable.
     */
    public const BITRIX_STATUS_UNAVAILABLE = 500;

    /**
     * Bitrix is available, but not have connection to DB.
     */
    public const BITRIX_STATUS_NO_DB_CONNECTION = 100;

    /**
     * Bitrix is available.
     */
    public const BITRIX_STATUS_COMPLETE = 0;

    /**
     * @var integer Status of Bitrix kernel. Value of constant `Application::BITRIX_STATUS_*`.
     */
    private $bitrixStatus = self::BITRIX_STATUS_UNAVAILABLE;

    /**
     * @var null|string
     */
    private $documentRoot = null;

    /**
     * Initialize kernel of Bitrix.
     *
     * @return int The status of readiness kernel.
     */
    public function initializeBitrix()
    {

        if ($this->bitrixStatus === static::BITRIX_STATUS_COMPLETE) {
            return static::BITRIX_STATUS_COMPLETE;
        } elseif (!$this->checkBitrix()) {
            return static::BITRIX_STATUS_UNAVAILABLE;
        }

        define('BITRIX_CLI', true);
        define('NO_KEEP_STATISTIC', true);
        define('NOT_CHECK_PERMISSIONS', true);
        define("LANGUAGE_ID", "pa");
        define("LOG_FILENAME", 'php://stderr');
        define("BX_NO_ACCELERATOR_RESET", true);
        define("STOP_STATISTICS", true);
        define("NO_AGENT_STATISTIC", "Y");
        define("NO_AGENT_CHECK", true);
        defined('PUBLIC_AJAX_MODE') || define('PUBLIC_AJAX_MODE', true);

        try {
            /**
             * Declare global legacy variables
             *
             * Including kernel here makes them local by default but some modules depend on them in installation class
             */

            global
            /** @noinspection PhpUnusedLocalVariableInspection */
            $DB, $DBType, $DBHost, $DBLogin, $DBPassword,
            $DBName, $DBDebug, $DBDebugToFile, $APPLICATION, $USER, $DBSQLServerType;

            require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

            if (defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED === true) {
                $this->bitrixStatus = static::BITRIX_STATUS_COMPLETE;
            }

            // Альтернативный способ вывода ошибок типа "DB query error.":
            $GLOBALS["DB"]->debug = true;

            $app = Application::getInstance();
            $con = $app->getConnection();
            $DB->db_Conn = $con->getResource();

            if (in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) === false) {
                echo 'Warning: The console should be invoked via the CLI version of PHP, not the '
                    . \PHP_SAPI . ' SAPI' . \PHP_EOL;
            }


        } catch (ConnectionException $e) {
            $this->bitrixStatus = static::BITRIX_STATUS_NO_DB_CONNECTION;
        }

        return $this->bitrixStatus;
    }

    /**
     * Checks readiness of Bitrix for kernel initialize.
     *
     * @return bool
     */
    public function checkBitrix()
    {
        if (!is_file($_SERVER['DOCUMENT_ROOT'] . '/bitrix/.settings.php')) {
            return false;
        }

        return true;
    }

    /**
     * Gets Bitrix status.
     *
     * @return int Value of constant `Application::BITRIX_STATUS_*`.
     */
    public function getBitrixStatus()
    {
        return $this->bitrixStatus;
    }

    /**
     * Checks that the Bitrix kernel is loaded.
     *
     * @return bool
     */
    public function isBitrixLoaded()
    {
        return $this->bitrixStatus === static::BITRIX_STATUS_COMPLETE;
    }


    /**
     * Sets path to the document root of site.
     *
     * @param string $dir Path to document root.
     */
    public function setDocumentRoot($dir)
    {
        $_SERVER['DOCUMENT_ROOT'] = $this->documentRoot = $dir;
    }

    /**
     * Gets document root of site.
     *
     * @return null|string
     */
    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }
}
