<?php

namespace Local\Util;

/**
 * Class PictureResizerInterface
 * @package Local\Util
 */
interface PictureResizerInterface
{
    /**
     * Ресайзнуть картинку.
     *
     * @return string
     */
    public function resizePicture() : string;

    /**
     * Ресайз картинки по URL.
     *
     * @param string $sUrlImage URL картинки.
     *
     * @return string
     */
    public function resizePictureByUrl(string $sUrlImage) : string;

    /**
     * Установить качество выходной картинки.
     *
     * @param integer $iJpgQuality Качество JPG.
     *
     * @return self
     */
    public function setJpgQuality(int $iJpgQuality);

    /**
     * Установить качество выходной картинки.
     *
     * @param integer $idImage Битриксовое ID картинки.
     *
     * @return self
     */
    public function setImageId(int $idImage);

    public function setWidth(int $width);

    public function setHeight(int $height);
}
