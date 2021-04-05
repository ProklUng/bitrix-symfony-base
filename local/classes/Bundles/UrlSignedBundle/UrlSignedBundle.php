<?php

namespace Local\Bundles\UrlSignedBundle;

use Local\Bundles\UrlSignedBundle\DependencyInjection\Compiler\SignerPass;
use Local\Bundles\UrlSignedBundle\DependencyInjection\UrlSignedExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class UrlSignedBundle
 * @package Local\Bundles\UrlSignedBundle
 *
 * @since 12.02.2021
 */
class UrlSignedBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new UrlSignedExtension();
        }

        return $this->extension;
    }

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new SignerPass());
    }
}
