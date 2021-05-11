<?php
namespace Local\Util;

use Intervention\Image\ImageManager;

/**
 * Class GutaResizeNoUpscale
 * Ресайз без upscale.
 * @package Local\Util
 */
class GutaResizeNoUpscale extends GutaResize
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
        $image = $imageManager->make($urlPicture);

        // Если картинка меньше размером, чем требуемая,
        // то вернем исходник.
        if ($image->width() <= $this->width
            &&
            $image->height() <= $this->height
        ) {
            return false;
        }

        // Ресайз и кроп
        $image->resize($this->width, $this->height, function ($constraint) {
            $constraint->upsize();
            $constraint->aspectRatio();
        });

        $destinationFilename = $_SERVER['DOCUMENT_ROOT'].$resultPath;
        // Сохранить результат.

        $image->save($destinationFilename, $this->jpgQuaility);

        return true;
    }
}
