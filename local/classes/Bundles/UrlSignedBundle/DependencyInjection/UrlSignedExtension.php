<?php

namespace Local\Bundles\UrlSignedBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Local\Bundles\UrlSignedBundle\UrlSigner\UrlSignerInterface;

/**
 * Class UrlSignedExtension
 * @package Local\Bundles\UrlSigned\DependencyInjection
 *
 * @since 12.02.2021
 */
class UrlSignedExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->registerForAutoconfiguration(UrlSignerInterface::class)->addTag('url_signer.signer');

        $container->setParameter('url_signer.signer', $config['signer']);
        $container->setParameter('url_signer.signature_key', $config['signature_key']);
        $container->setParameter('url_signer.default_expiration', $config['default_expiration']);
        $container->setParameter('url_signer.expires_parameter', $config['expires_parameter']);
        $container->setParameter('url_signer.signature_parameter', $config['signature_parameter']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

    }

    /**
     * @inheritDoc
     */
    public function getAlias() : string
    {
        return 'url_signer';
    }
}
