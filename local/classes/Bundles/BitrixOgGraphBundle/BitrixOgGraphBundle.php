<?php

namespace Local\Bundles\BitrixOgGraphBundle;

use Local\Bundles\BitrixOgGraphBundle\DependencyInjection\BitrixOgGraphExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BitrixOgGraphBundle
 * @package Local\Bundles\BitrixOgGraphBundle
 *
 * @since 19.02.2021
 */
class BitrixOgGraphBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new BitrixOgGraphExtension();
        }

        return $this->extension;
    }
}
