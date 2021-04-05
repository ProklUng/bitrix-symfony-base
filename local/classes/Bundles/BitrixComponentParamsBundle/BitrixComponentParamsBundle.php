<?php

namespace Local\Bundles\BitrixComponentParamsBundle;

use Local\Bundles\BitrixComponentParamsBundle\DependencyInjection\BitrixComponentParamsExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BitrixComponentParamsBundle
 * @package Local\Bundles\BitrixComponentParamsBundle
 *
 * @since 26.02.2021
 */
class BitrixComponentParamsBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new BitrixComponentParamsExtension();
        }

        return $this->extension;
    }
}
