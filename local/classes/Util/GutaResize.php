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
 */
class GutaResize implements PictureResizerInterface
{
    /** @const string PATH_TO_RESIZED_IMAGES Путь, куда складываются ресайзнутые картинки. */
    private const PATH_TO_RESIZED_IMAGES = '/upload/resized';

    /** @var int $iWidth Нужная ширина картинки. */
    protected $iWidth = 0;
    /** @var int $iHeight Нужная высота картинки. */
    protected $iHeight = 0;
    /** @var int $iJpgQuaility Выходное качество JPG. */
    protected $iJpgQuaility = 85;
    /** @var int $iImageId ID картинки. */
    protected $iImageId = 0;

    /** @var ImageManager $obImageManager Ресайзер. */
    protected $obImageManager;

    /**
     * ImageResizer constructor.
     *
     * @param mixed $iImageId ID картинки.
     * @param mixed $iWidth   Нужная ширина картинки, по умолчанию 1920.
     * @param mixed $iHeight  Нужная высота, по умолчанию 1080.
     */
    public function __construct($iImageId = null, $iWidth = 1920, $iHeight = 1080)
    {
        $this->iImageId = $iImageId;
        $this->iWidth = $iWidth;
        $this->iHeight = $iHeight;

        // Create an image manager instance with favored driver (default)
        $this->obImageManager = new ImageManager();
    }

    /**
     * Фасад ресайза по ID.
     *
     * @param integer $iImageId ID битриксовской картинки.
     * @param mixed   $iWidth   Ширина нужной картинки. По умолчанию - 1920.
     * @param mixed   $iHeight  Ширина нужной картинки. По умолчанию - 1080.
     *
     * @return string
     */
    public static function resize(int $iImageId, $iWidth = 1920, $iHeight = 1080) : string
    {
        if (!$iImageId) {
            return '';
        }

        $obResizer = new static ($iImageId, $iWidth, $iHeight);

        return $obResizer->resizePicture();
    }

    /**
     * Установить качество выходной картинки.
     *
     * @param integer $iJpgQuality Качество JPG.
     *
     * @return self
     */
    public function setJpgQuality(int $iJpgQuality) : self
    {
        $this->iJpgQuaility = $iJpgQuality;
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
        $this->iImageId = $idImage;

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
        $this->iWidth = $width;

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
        $this->iHeight = $height;

        return $this;
    }

    /**
     * Фасад ресайза по URL.
     *
     * @param string  $sUrl    URL картинки.
     * @param integer $iWidth  Ширина нужной картинки.
     * @param integer $iHeight Ширина нужной картинки.
     *
     * @return string
     */
    public static function url(string $sUrl = '', int $iWidth = 0, int $iHeight = 0) : string
    {
        $obResizer = new static (0, $iWidth, $iHeight);

        return $obResizer->resizePictureByUrl($sUrl);
    }

    /**
     * Ресайзнуть картинку.
     *
     * @return string
     */
    public function resizePicture() : string
    {
        // Получить URL картинки.
        $sPath = $this->getPathImageById($this->iImageId);

        return $this->resizePictureByUrl($sPath);
    }

    /**
     * Ресайз картинки по URL.
     *
     * @param string $sUrlImage URL картинки.
     *
     * @return string
     */
    public function resizePictureByUrl(string $sUrlImage) : string
    {
        $sUrlPicture = $_SERVER['DOCUMENT_ROOT'].$sUrlImage;
        // Получить имя результирующего файла.
        $sResultPath = $this->getDestinationFileName($sUrlPicture);

        // Если картинка уже существует, то не нужно ничего ресайзить.
        if (!$this->needResize($sResultPath)) {
            return $sResultPath;
        }

        // Проверить директорию для ресайзнутых картинок на существование.
        $this->checkExistUploadDirectory();

        if ($this->interventionResize($sUrlPicture, $sResultPath)) {
            return $sResultPath;
        }

        return $sUrlImage;
    }

    /**
     * Сам процесс ресайза (для наследования).
     *
     * @param string $sUrlPicture URL исходной картинки.
     * @param string $sResultPath Результирующий путь.
     *
     * @return boolean
     */
    protected function interventionResize(string $sUrlPicture, string $sResultPath): bool
    {
        $obImage = $this->obImageManager->make($sUrlPicture);

        // Если картинка меньше размером, чем требуемая,
        // то вернем исходник.
        if (!$this->checkSize($obImage)) {
            return false;
        }

        // Ресайз и кроп
        $obImage->fit($this->iWidth, $this->iHeight);

        $sDestinationFilename = $_SERVER['DOCUMENT_ROOT'].$sResultPath;
        // Сохранить результат.
        $obImage->save($sDestinationFilename, $this->iJpgQuaility);

        return true;
    }

    /**
     * Проверка размеров картинок.
     *
     * @param Image $obImage
     *
     * @return boolean
     */
    protected function checkSize(Image $obImage) : bool
    {
        // Если картинка меньше размером, чем требуемая,
        // то вернем исходник.
        if ($obImage->width() <= $this->iWidth
            &&
            $obImage->height() <= $this->iHeight
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
        $sPathToUploadDirectory = $_SERVER['DOCUMENT_ROOT'].self::PATH_TO_RESIZED_IMAGES;

        if (!is_dir($sPathToUploadDirectory)) {
            @mkdir($sPathToUploadDirectory);
            @chmod($sPathToUploadDirectory, 0755);
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
     * @param string $sOriginalUrl URL оригинальной картинки.
     *
     * @return string
     */
    protected function getDestinationFileName(string $sOriginalUrl) : string
    {

        $sMd5 = md5($sOriginalUrl.$this->iWidth.$this->iHeight);
        $sExtension = pathinfo($sOriginalUrl, PATHINFO_EXTENSION);

        return self::PATH_TO_RESIZED_IMAGES.'/'.$sMd5 . '.' . $sExtension;
    }

    /**
     * Получить путь к картине по ID Битрикса.
     *
     * @param integer $iID ID битриксовской картинки.
     *
     * @return string
     */
    protected function getPathImageById(int $iID): string
    {
        return CFile::GetPath($iID) ?? '';
    }

    /**
     * Нужно ли делать ресайз (если результирующая картинка уже существует, то нет).
     *
     * @param string $sDestinationFilePath Путь к результирущей картинке.
     *
     * @return boolean
     */
    protected function needResize(string $sDestinationFilePath) : bool
    {

        if (!@file_exists($_SERVER['DOCUMENT_ROOT'].$sDestinationFilePath)) {
            return true;
        }

        return false;
    }
}
