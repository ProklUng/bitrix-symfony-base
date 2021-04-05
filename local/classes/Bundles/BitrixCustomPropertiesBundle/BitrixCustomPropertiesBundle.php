<?php

namespace Local\Bundles\BitrixCustomPropertiesBundle;

use Local\Bundles\BitrixCustomPropertiesBundle\DependencyInjection\BitrixCustomPropertiesExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BitrixCustomPropertiesBundle
 * @package Local\Bundles\BitrixCustomPropertiesBundle
 *
 * @since 10.02.2021
 */
class BitrixCustomPropertiesBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new BitrixCustomPropertiesExtension();
        }

        return $this->extension;
    }
}
