<?php

namespace Local\Tests\Invokers;

use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use CUser;
use Exception;

/**
 * Class Module
 * pattern Singleton
 */
class Module
{
    const ITEMS_ID = 'ws_bunit_menu';
    const MODULE_NAME = 'ws.bunit';
    const FALLBACK_LOCALE = 'ru';

    private static $name = self::MODULE_NAME;

    /**
     * @return Application
     * @throws SystemException
     */
    public function application()
    {
        return Application::getInstance();
    }

    /**
     * Will get module facade
     * @return Module
     */
    public static function getInstance()
    {
        static $self = null;
        if (!$self) {
            $self = new self;
        }

        return $self;
    }

    /**
     * @return CUser
     */
    public function getUser()
    {
        global $USER;

        return $USER;
    }

    public static function getName($stripDots = false)
    {
        $name = static::$name;
        if ($stripDots) {
            $name = str_replace('.', '_', $name);
        }

        return $name;
    }

    /**
     * @return string
     */
    public static function getBitrixPath()
    {
        return realpath(__DIR__."/../../../../bitrix");
    }

    /**
     * @param string $path
     * @throws Exception
     */
    public static function safeInclude($path)
    {
        if (!file_exists($path) || is_dir($path)) {
            throw new Exception("Path of include file is wrong");
        }
        include $path;
    }
}
