<?php

namespace Local\Bundles\FrameworkExtensionBundle;

use Local\Bundles\FrameworkExtensionBundle\DependencyInjection\FrameworkExtensionExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class FrameworkExtensionBundle
 * @package Local\Bundles\FrameworkExtensionBundle
 *
 * @since 13.04.2021
 */
class FrameworkExtensionBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new FrameworkExtensionExtension();
        }

        return $this->extension;
    }
}
