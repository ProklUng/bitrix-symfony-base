<?php

namespace Local\Bundles\BitrixIblockElementValidatorBundle;

use Local\Bundles\BitrixIblockElementValidatorBundle\DependencyInjection\BitrixIblockElementValidatorBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BitrixIblockElementValidatorBundle
 * @package Local\Bundles\BitrixIblockElementValidatorBundle
 *
 * @since 07.02.2021
 */
class BitrixIblockElementValidatorBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new BitrixIblockElementValidatorBundleExtension();
        }

        return $this->extension;
    }
}
