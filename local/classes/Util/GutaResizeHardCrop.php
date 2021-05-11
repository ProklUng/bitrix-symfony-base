<?php
namespace Local\Util;

use Intervention\Image\ImageManager;

/**
 * Class GutaResizeHardCrop
 * Ресайз без upscale.
 * @package Local\Util
 */
class GutaResizeHardCrop extends GutaResize
{
    /**
     * Ресайз без upscale (увеличения размера,
     * в случае, если картинка маленькая).
     *
     * @param string $urlPicture URL исходной картинки.
     * @param string $resultPath Результирующий путь.
     *
     * @return boolean
     */
    protected function interventionResize(string $urlPicture, string $resultPath): bool
    {
        // Create an image manager instance with favored driver (default)
        $imageManager = new ImageManager();
        $imageHandler = $imageManager->make($urlPicture);

        // Если картинка меньше размером, чем требуемая,
        // то вернем исходник.
        if ($imageHandler->width() <= $this->width
            &&
            $imageHandler->height() <= $this->height
        ) {
            return false;
        }

        $imageHandler->fit($this->width, $this->height);

        // Ресайз и кроп
        $imageHandler->crop($this->width, $this->height);

        $destinationFilename = $_SERVER['DOCUMENT_ROOT'].$resultPath;
        // Сохранить результат.

        $imageHandler->save($destinationFilename, $this->jpgQuaility);

        return true;
    }
}
