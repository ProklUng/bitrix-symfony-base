<?php

namespace Local\Bundles\FacadeBundle;

use Local\Bundles\FacadeBundle\DependencyInjection\CompilerPass\AddFacadePass;
use Local\Bundles\FacadeBundle\DependencyInjection\FacadeExtension;
use Local\Bundles\FacadeBundle\Services\AbstractFacade;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class FacadeBundle
 * @package Local\Bundles\FacadeBundle
 *
 * @since 15.04.2021
 */
class FacadeBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new FacadeExtension();
        }

        return $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function boot() : void
    {
        parent::boot();

        /** @var ContainerInterface $facadeContainer */
        $facadeContainer = $this->container->get('laravel.facade.container');
        AbstractFacade::setFacadeContainer($facadeContainer);
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container) : void
    {
        parent::build($container);

        $container->addCompilerPass(new AddFacadePass());
    }
}
