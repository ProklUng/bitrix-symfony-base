<?php

namespace Local\Bundles\BundleMakerBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class BundleMakerBundleExtension
 * @package Local\Bundles\BundleMakerBundle\DependencyInjection
 */
class BundleMakerBundleExtension extends Extension
{

    /** Setting config to service
     *
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @return void
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . "/../Resources/config"));
        $configuration = new Configuration();
        $loader->load('services.xml');
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('netbrothers_nbcsb', $config);
        $createBundleCommand = $container->getDefinition('netbrothers_nbcsb.command.create_bundle_command');
        $createBundleCommand->setArgument(0, $container->getParameter('netbrothers_nbcsb'));
    }

    /**
     * @return string
     */
    public function getAlias() : string
    {
        return 'netbrothers_nbcsb';
    }
}
