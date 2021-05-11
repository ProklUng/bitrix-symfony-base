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
     * @param Image $imageHandler
     *
     * @return boolean
     */
    protected function checkSize(Image $imageHandler) : bool
    {
        return true;
    }
}
