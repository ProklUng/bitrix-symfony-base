<?php

namespace Local\Util;

use CFile;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

/**
 * Class GutaResize
 * Ресайз картинок с помощью библиотеки
 * Intervention Image.
 * @package Local\Util
 *
 * @since 03.02.2021 Избавление от венгерской нотации.
 */
class GutaResize implements PictureResizerInterface
{
    /** @const string PATH_TO_RESIZED_IMAGES Путь, куда складываются ресайзнутые картинки. */
    private const PATH_TO_RESIZED_IMAGES = '/upload/resized';

    /**
     * @var ImageManager $imageManager Ресайзер.
     */
    protected $imageManager;

    /**
     * @var integer $width Нужная ширина картинки.
     */
    protected $width = 0;

    /**
     * @var integer $height Нужная высота картинки.
     */
    protected $height = 0;

    /**
     * @var integer $jpgQuaility Выходное качество JPG.
     */
    protected $jpgQuaility = 85;

    /**
     * @var integer $imageId ID картинки.
     */
    protected $imageId = 0;

    /**
     * ImageResizer constructor.
     *
     * @param integer|null $imageId ID картинки.
     * @param mixed        $width   Нужная ширина картинки, по умолчанию 1920.
     * @param mixed        $height  Нужная высота, по умолчанию 1080.
     */
    public function __construct($imageId = null, $width = 1920, $height = 1080)
    {
        $this->imageId = $imageId;
        $this->width = $width;
        $this->height = $height;

        // Create an image manager instance with favored driver (default)
        $this->imageManager = new ImageManager();
    }

    /**
     * Фасад ресайза по ID.
     *
     * @param integer $imageId ID битриксовской картинки.
     * @param mixed   $width   Ширина нужной картинки. По умолчанию - 1920.
     * @param mixed   $height  Ширина нужной картинки. По умолчанию - 1080.
     *
     * @return string
     */
    public static function resize(int $imageId, $width = 1920, $height = 1080) : string
    {
        if (!$imageId) {
            return '';
        }

        $imageResizer = new static ($imageId, $width, $height);

        return $imageResizer->resizePicture();
    }

    /**
     * Установить качество выходной картинки.
     *
     * @param integer $jpgQuality Качество JPG.
     *
     * @return self
     */
    public function setJpgQuality(int $jpgQuality) : self
    {
        $this->jpgQuaility = $jpgQuality;
        return $this;
    }

    /**
     * Установить качество выходной картинки.
     *
     * @param integer $idImage Битриксовое ID картинки.
     *
     * @return self
     */
    public function setImageId(int $idImage)
    {
        $this->imageId = $idImage;

        return $this;
    }

    /**
     * Ширина.
     *
     * @param integer $width
     *
     * @return $this
     */
    public function setWidth(int $width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Высота.
     *
     * @param integer $height
     *
     * @return $this
     */
    public function setHeight(int $height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Фасад ресайза по URL.
     *
     * @param string  $url    URL картинки.
     * @param integer $width  Ширина нужной картинки.
     * @param integer $height Ширина нужной картинки.
     *
     * @return string
     */
    public static function url(string $url = '', int $width = 0, int $height = 0) : string
    {
        $resizer = new static (0, $width, $height);

        return $resizer->resizePictureByUrl($url);
    }

    /**
     * Ресайзнуть картинку.
     *
     * @return string
     */
    public function resizePicture() : string
    {
        // Получить URL картинки.
        $path = $this->getPathImageById($this->imageId);

        return $this->resizePictureByUrl($path);
    }

    /**
     * Ресайз картинки по URL.
     *
     * @param string $urlImage URL картинки.
     *
     * @return string
     */
    public function resizePictureByUrl(string $urlImage) : string
    {
        $urlPicture = $_SERVER['DOCUMENT_ROOT'] . $urlImage;
        // Получить имя результирующего файла.
        $resultPath = $this->getDestinationFileName($urlPicture);

        // Если картинка уже существует, то не нужно ничего ресайзить.
        if (!$this->needResize($resultPath)) {
            return $resultPath;
        }

        // Проверить директорию для ресайзнутых картинок на существование.
        $this->checkExistUploadDirectory();

        if ($this->interventionResize($urlPicture, $resultPath)) {
            return $resultPath;
        }

        return $urlImage;
    }

    /**
     * Сам процесс ресайза (для наследования).
     *
     * @param string $urlPicture URL исходной картинки.
     * @param string $resultPath Результирующий путь.
     *
     * @return boolean
     */
    protected function interventionResize(string $urlPicture, string $resultPath): bool
    {
        $imageHandler = $this->imageManager->make($urlPicture);

        // Если картинка меньше размером, чем требуемая,
        // то вернем исходник.
        if (!$this->checkSize($imageHandler)) {
            return false;
        }

        // Ресайз и кроп
        $imageHandler->fit($this->width, $this->height);

        $destinationFilename = $_SERVER['DOCUMENT_ROOT'].$resultPath;
        // Сохранить результат.
        $imageHandler->save($destinationFilename, $this->jpgQuaility);

        return true;
    }

    /**
     * Проверка размеров картинок.
     *
     * @param Image $imageHandler
     *
     * @return boolean
     */
    protected function checkSize(Image $imageHandler) : bool
    {
        // Если картинка меньше размером, чем требуемая,
        // то вернем исходник.
        if ($imageHandler->width() <= $this->width
            &&
            $imageHandler->height() <= $this->height
        ) {
            return false;
        }

        return true;
    }

    /**
     * Проверка на существовании директории для загруженных
     * картинок. Если она не существует, то создать ее.
     *
     * @return void
     */
    protected function checkExistUploadDirectory() : void
    {
        // Путь к директории, где расположатся ресайзнутые файлы.
        $pathToUploadDirectory = $_SERVER['DOCUMENT_ROOT'] . self::PATH_TO_RESIZED_IMAGES;

        if (!is_dir($pathToUploadDirectory)) {
            @mkdir($pathToUploadDirectory);
            @chmod($pathToUploadDirectory, 0755);
        }
    }

    /**
     * Получить название результирующего файла.
     *
     * {@internal
     *     Принцип: исходный путь, запрошенные ширина и высота
     *     уникализируется с помощью MD5. Затем к полученной
     *     строке прибавляется исходное расширение картинки.
     * }}
     *
     * @param string $originalUrl URL оригинальной картинки.
     *
     * @return string
     */
    protected function getDestinationFileName(string $originalUrl) : string
    {
        $md5 = md5($originalUrl .$this->width . $this->height);
        $extension = pathinfo($originalUrl, PATHINFO_EXTENSION);

        return self::PATH_TO_RESIZED_IMAGES . '/' . $md5 . '.' . $extension;
    }

    /**
     * Получить путь к картине по ID Битрикса.
     *
     * @param integer $id ID битриксовской картинки.
     *
     * @return string
     */
    protected function getPathImageById(int $id): string
    {
        return CFile::GetPath($id) ?? '';
    }

    /**
     * Нужно ли делать ресайз (если результирующая картинка уже существует, то нет).
     *
     * @param string $destinationFilePath Путь к результирущей картинке.
     *
     * @return boolean
     */
    protected function needResize(string $destinationFilePath) : bool
    {
        if (!@file_exists($_SERVER['DOCUMENT_ROOT'] . $destinationFilePath)) {
            return true;
        }

        return false;
    }
}
