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
     * @param string $sUrlPicture URL исходной картинки.
     * @param string $sResultPath Результирующий путь.
     *
     * @return boolean
     */
    protected function interventionResize(string $sUrlPicture, string $sResultPath): bool
    {
        // Create an image manager instance with favored driver (default)
        $obImageManager = new ImageManager();
        $obImage = $obImageManager->make($sUrlPicture);

        // Если картинка меньше размером, чем требуемая,
        // то вернем исходник.
        if ($obImage->width() <= $this->iWidth
            &&
            $obImage->height() <= $this->iHeight
        ) {
            return false;
        }

        // Ресайз и кроп
        $obImage->resize($this->iWidth, $this->iHeight, function ($constraint) {
            $constraint->upsize();
            $constraint->aspectRatio();
        });

        $sDestinationFilename = $_SERVER['DOCUMENT_ROOT'].$sResultPath;
        // Сохранить результат.

        $obImage->save($sDestinationFilename, $this->iJpgQuaility);

        return true;
    }
}
