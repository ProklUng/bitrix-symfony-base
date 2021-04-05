<?php

namespace Local\Tests;

/**
 * Class PHPUnitSnapData
 * @package Local\Tests
 */
class PHPUnitSnaps
{
    private const SNAP_DIRECTORY = DIRECTORY_SEPARATOR . '/local/classes/Tests'
    . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'snaps';

    /**
     * Сделать snap снимок данных.
     *
     * @param string $fileName
     * @param mixed $value
     *
     * @return boolean
     */
    public static function makeSnap(
        string $fileName,
        $value
    ): bool {
        $path = $_SERVER['DOCUMENT_ROOT'] . self::SNAP_DIRECTORY .DIRECTORY_SEPARATOR . $fileName;
        if (file_exists($path)) {
            unlink($path);
        }

        $content = json_encode($value);

        $result = file_put_contents($path, $content);

        return $result !== false;
    }

    /**
     * Сделать snap снимок данных. Массив объектов.
     *
     * @param string $fileName
     * @param mixed $value
     *
     * @return boolean
     */
    public static function makeSnapArrayObjects(
        string $fileName,
        $value
    ): bool {
        $path = $_SERVER['DOCUMENT_ROOT'] . self::SNAP_DIRECTORY .DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($path)) {
            unlink($path);
        }

        $content = serialize($value);

        $result = file_put_contents($path, $content);

        return $result !== false;
    }

    /**
     * Загрузить snap.
     *
     * @param string $fileName
     *
     * @return bool|mixed
     */
    public static function loadSnap(string $fileName)
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . self::SNAP_DIRECTORY . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($path)) {
            return false;
        }

        $content = file_get_contents($path);

        return json_decode($content, true, 512);
    }

    /**
     * Загрузить snap.
     *
     * @param string $fileName
     *
     * @return bool|mixed
     */
    public static function loadSnapArrayObjects(string $fileName)
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . self::SNAP_DIRECTORY . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($path)) {
            return false;
        }

        $content = file_get_contents($path);

        return unserialize($content);
    }

    /**
     * Уже существует?
     *
     * @param string $fileName
     *
     * @return bool
     */
    public static function hasSnap(string $fileName) : bool
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . self::SNAP_DIRECTORY . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($path)) {
            return false;
        }

        return true;
    }
}
