<?php

namespace Local\Bundles\BitrixOgGraphBundle\Services\Utils;

use CFile;

/**
 * Class CFileWrapper
 * @package Local\Bundles\BitrixOgGraphBundle\Services\Utils
 *
 * @method static ResizeImageGet($file, array $arSize, int $resizeType,bool $bInitSizes = false,array $arFilters = false,bool $bImmediate = false,bool $jpgQuality = false)
 */
class CFileWrapper
{
    /**
     * @var CFile Битриксовый CFile.
     */
    private $file;

    /**
     * CFileWrapper constructor.
     *
     * @param CFile $file Битриксовый CFile.
     */
    public function __construct(
        CFile $file
    ) {
        $this->file = $file;
    }

    /**
     * Путь к файлу.
     *
     * @param integer $imageId ID картинки.
     *
     * @return string
     */
    public function path(int $imageId) : string
    {
        $result =  $this->file::GetPath($imageId);

        return $result ?? '';
    }

    /**
     * @param string $method    Метод.
     * @param mixed  $arguments Аргументы.
     *
     * @return mixed
     */
    public function __call(string $method, $arguments)
    {
        return $this->file->$method(...$arguments);
    }

    /**
     * @param string $method    Метод.
     * @param mixed  $arguments Аргументы.
     *
     * @return mixed
     */
    public static function __callStatic(string $method, $arguments)
    {
        return CFile::$method(...$arguments);
    }
}
