<?php

namespace Local\Bundles\ModelBundle;

use Local\Bundles\ModelBundle\DependencyInjection\CompilerPass\AddModelsIblockPass;
use Local\Bundles\ModelBundle\DependencyInjection\ModelExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ModelBundle
 * @package Local\Bundles\ModelBundle
 *
 * @since 30.01.2021
 */
class ModelBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new ModelExtension();
        }

        return $this->extension;
    }

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddModelsIblockPass());
    }
}
