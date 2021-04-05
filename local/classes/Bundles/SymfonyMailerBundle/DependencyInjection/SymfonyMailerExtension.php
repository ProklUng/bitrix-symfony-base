<?php

namespace Local\Bundles\SymfonyMailerBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailTransportFactory;
use Symfony\Component\Mailer\Mailer;

/**
 * Class SymfonyMailerExtension
 * @package Local\Bundles\SymfonyMailer\DependencyInjection
 *
 * @since 02.03.2021
 */
class SymfonyMailerExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        if (!class_exists(Mailer::class)) {
            throw new LogicException(
                'Mailer support cannot be enabled as the component is not installed. 
                Try running "composer require symfony/mailer".'
            );
        }

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!$config['enabled']) {
            return;
        }

        $container->setParameter(
            'symfony_mailer_bundle.default_email_from_adress',
            $config['default_email_from_adress']
        );
        $container->setParameter('symfony_mailer_bundle.default_email_from_title', $config['default_email_from_title']);
        $container->setParameter('symfony_mailer_bundle.admin_email', $config['admin_email']);
        $container->setParameter('symfony_mailer_bundle.dsn', $config['dsn']);
        $container->setParameter('symfony_mailer_bundle.dsn_file', $config['dsn_file']);
        $container->setParameter('symfony_mailer_bundle.envelope', $config['envelope']);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.self::DIR_CONFIG)
        );

        $loader->load('services.yaml');
        $loader->load('transport.yaml');

        $this->registerMailerConfiguration($config, $container);
    }

    /**
     * Регистрация мэйлера.
     *
     * @param array            $config    Конфиг.
     * @param ContainerBuilder $container Контейнер.
     *
     * @return void
     */
    public function registerMailerConfiguration(array $config, ContainerBuilder $container): void
    {
        // Если задан параметр mock_sending_email - подключить транспорт
        // записи в файл.
        if ($config['mock_sending_email']) {
            $mailService = $container->getDefinition('mailer_bundle.mail_service');
            $fileTransportEmailer = $container->getDefinition('mailer_bundle.mailer_debug');
            $mailService->replaceArgument(0, $fileTransportEmailer);
        }

        $recipients = $config['envelope']['recipients'] ?? null;
        $sender = $config['envelope']['sender'] ?? null;

        $envelopeListener = $container->getDefinition('mailer.envelope_listener');
        $envelopeListener->setArgument(0, $sender);
        $envelopeListener->setArgument(1, $recipients);
    }

    /**
     * @inheritDoc
     */
    public function getAlias(): string
    {
        return 'symfony_mailer';
    }
}
