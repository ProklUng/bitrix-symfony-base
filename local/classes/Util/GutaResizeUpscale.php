<?php

namespace Local\Util;

use Intervention\Image\Image;

/**
 * Class GutaResizeUpscale
 * Upscale изображения вне зависимости от его размеров.
 * @package Local\Util
 */
class GutaResizeUpscale extends GutaResize
{
    /**
     * Проверка размеров картинок.
     *
     * @param Image $obImage
     *
     * @return boolean
     */
    protected function checkSize(Image $obImage) : bool
    {
        return true;
    }
}
