<?php

namespace Local\Bundles\BitrixDatabaseBundle;

use Local\Bundles\BitrixDatabaseBundle\DependencyInjection\BitrixDatabaseExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BitrixDatabaseBundle
 * @package Local\Bundles\BitrixDatabaseBundle
 *
 * @since 08.04.2021
 */
class BitrixDatabaseBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new BitrixDatabaseExtension();
        }

        return $this->extension;
    }
}
