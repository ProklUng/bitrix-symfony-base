<?php

namespace Local\Bundles\BitrixUtilsBundle;

use Local\Bundles\BitrixUtilsBundle\DependencyInjection\BitrixUtilsExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BitrixUtilsBundle
 * @package Local\Bundles\BitrixUtilsBundle
 *
 * @since 11.03.2021
 */
class BitrixUtilsBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new BitrixUtilsExtension();
        }

        return $this->extension;
    }
}
