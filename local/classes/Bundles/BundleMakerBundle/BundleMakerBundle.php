<?php

namespace Local\Bundles\BundleMakerBundle;

use Local\Bundles\BundleMakerBundle\DependencyInjection\BundleMakerBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BundleMakerBundle
 * @package Local\Bundles\BundleMakerBundle
 */
class BundleMakerBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function getContainerExtension() : BundleMakerBundleExtension
    {
        if (null === $this->extension) {
            $this->extension = new BundleMakerBundleExtension();
        }

        return $this->extension;
    }
}
