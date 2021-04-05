<?php

namespace Local\Bundles\DtoMapperBundle;

use Local\Bundles\DtoMapperBundle\DependencyInjection\DtoMapperExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class DtoMapperBundle
 * @package Local\Bundles\DtoMapperBundle
 *
 * @since 26.02.2021
 */
class DtoMapperBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new DtoMapperExtension();
        }

        return $this->extension;
    }
}
