<?php

namespace Local\Bundles\StaticPageMakerBundle;

use Local\Bundles\StaticPageMakerBundle\DependencyInjection\StaticPageMakerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class StaticPageMakerBundle
 * @package Local\Bundles\StaticPageMakerBundle
 *
 * @since 23.01.2021
 */
class StaticPageMakerBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new StaticPageMakerExtension();
        }

        return $this->extension;
    }
}
