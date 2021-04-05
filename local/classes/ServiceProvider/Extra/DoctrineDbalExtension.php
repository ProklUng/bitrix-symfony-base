<?php

namespace Local\ServiceProvider\Extra;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\ImportDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Dbal\ManagerRegistryAwareConnectionProvider;
use Doctrine\Bundle\DoctrineBundle\Dbal\RegexSchemaAssetFilter;
use Doctrine\DBAL\Connections\MasterSlaveConnection;
use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\DBAL\Logging\LoggerChain;
use Doctrine\DBAL\SQLParserUtils;
use Doctrine\DBAL\Tools\Console\Command\ImportCommand;
use Doctrine\DBAL\Tools\Console\ConnectionProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class DoctrineDbalExtension
 * @package Local\ServiceProvider\Extra
 *
 * @since 16.12.2020 (fork)
 */
class DoctrineDbalExtension
{
    /** @var string */
    private $defaultConnection;

    /**
     * Loads the DBAL configuration.
     *
     * Usage example:
     *
     *      <doctrine:dbal id="myconn" dbname="sfweb" user="root" />
     *
     * @param array $config An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @throws \Exception
     */
    public function dbalLoad(array $config, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            return;
        }

        $loader = new XmlFileLoader($container, new FileLocator($_SERVER['DOCUMENT_ROOT'] . '/local/configs/services'));
        $loader->load('dbal.xml');
        $chainLogger = $container->getDefinition('doctrine.dbal.logger.chain');
        $logger      = new Reference('doctrine.dbal.logger');
        if (! method_exists(SQLParserUtils::class, 'getPositionalPlaceholderPositions') && method_exists(LoggerChain::class, 'addLogger')) {
            // doctrine/dbal < 2.10.0
            $chainLogger->addMethodCall('addLogger', [$logger]);
        } else {
            $chainLogger->addArgument([$logger]);
        }

        if (class_exists(ImportCommand::class)) {
            $container->register('doctrine.database_import_command', ImportDoctrineCommand::class)
                ->addTag('console.command', ['command' => 'doctrine:database:import']);
        }

        if (empty($config['default_connection'])) {
            $keys                         = array_keys($config['connections']);
            $config['default_connection'] = reset($keys);
        }

        $this->defaultConnection = $config['default_connection'];

        $container->setAlias('database_connection', sprintf('doctrine.dbal.%s_connection', $this->defaultConnection));
        $container->getAlias('database_connection')->setPublic(true);
        $container->setAlias('doctrine.dbal.event_manager', new Alias(sprintf('doctrine.dbal.%s_connection.event_manager', $this->defaultConnection), false));

        $config['types'] = !empty($config['types']) ? $config['types'] : [];

        $container->setParameter('doctrine.dbal.connection_factory.types', $config['types']);

        $connections = [];

        foreach (array_keys($config['connections']) as $name) {
            $connections[$name] = sprintf('doctrine.dbal.%s_connection', $name);
        }

        $container->setParameter('doctrine.connections', $connections);
        $container->setParameter('doctrine.default_connection', $this->defaultConnection);

        foreach ($config['connections'] as $name => $connection) {
            $this->loadDbalConnection($name, $connection, $container);
        }
    }

    /**
     * Loads a configured DBAL connection.
     *
     * @param string           $name       The name of the connection
     * @param array            $connection A dbal connection configuration.
     * @param ContainerBuilder $container  A ContainerBuilder instance
     */
    protected function loadDbalConnection($name, array $connection, ContainerBuilder $container)
    {
        $configuration = $container->setDefinition(sprintf('doctrine.dbal.%s_connection.configuration', $name), new ChildDefinition('doctrine.dbal.connection.configuration'));
        $logger        = null;
        if ($connection['logging']) {
            $logger = new Reference('doctrine.dbal.logger');
        }

        unset($connection['logging']);

        if ($connection['profiling']) {
            $profilingAbstractId = $connection['profiling_collect_backtrace'] ?
                'doctrine.dbal.logger.backtrace' :
                'doctrine.dbal.logger.profiling';

            $profilingLoggerId = $profilingAbstractId . '.' . $name;
            $container->setDefinition($profilingLoggerId, new ChildDefinition($profilingAbstractId));
            $profilingLogger = new Reference($profilingLoggerId);
            $container->getDefinition('data_collector.doctrine')
                ->addMethodCall('addLogger', [$name, $profilingLogger])
                ->replaceArgument(1, $connection['profiling_collect_schema_errors']);

            if ($logger !== null) {
                $chainLogger = $container->register(
                    'doctrine.dbal.logger.chain',
                    LoggerChain::class
                );
                if (! method_exists(SQLParserUtils::class, 'getPositionalPlaceholderPositions') && method_exists(LoggerChain::class, 'addLogger')) {
                    // doctrine/dbal < 2.10.0
                    $chainLogger->addMethodCall('addLogger', [$profilingLogger]);
                } else {
                    $chainLogger->addArgument([$logger, $profilingLogger]);
                }

                $loggerId = 'doctrine.dbal.logger.chain.' . $name;
                $container->setDefinition($loggerId, $chainLogger);
                $logger = new Reference($loggerId);
            } else {
                $logger = $profilingLogger;
            }
        }

        unset(
            $connection['profiling'],
            $connection['profiling_collect_backtrace'],
            $connection['profiling_collect_schema_errors']
        );

        if (isset($connection['auto_commit'])) {
            $configuration->addMethodCall('setAutoCommit', [$connection['auto_commit']]);
        }

        unset($connection['auto_commit']);

        if (isset($connection['schema_filter']) && $connection['schema_filter']) {
            $definition = new Definition(RegexSchemaAssetFilter::class, [$connection['schema_filter']]);
            $definition->addTag('doctrine.dbal.schema_filter', ['connection' => $name]);
            $container->setDefinition(sprintf('doctrine.dbal.%s_regex_schema_filter', $name), $definition);
        }

        unset($connection['schema_filter']);

        if ($logger) {
            $configuration->addMethodCall('setSQLLogger', [$logger]);
        }

        // event manager
        $container->setDefinition(sprintf('doctrine.dbal.%s_connection.event_manager', $name), new ChildDefinition('doctrine.dbal.connection.event_manager'));

        // connection
        $options = $this->getConnectionOptions($connection);

        $def = $container
            ->setDefinition(sprintf('doctrine.dbal.%s_connection', $name), new ChildDefinition('doctrine.dbal.connection'))
            ->setPublic(true)
            ->setArguments([
                $options,
                new Reference(sprintf('doctrine.dbal.%s_connection.configuration', $name)),
                new Reference(sprintf('doctrine.dbal.%s_connection.event_manager', $name)),
                $connection['mapping_types'] ?? [],
            ]);

        // Set class in case "wrapper_class" option was used to assist IDEs
        if (isset($options['wrapperClass'])) {
            $def->setClass($options['wrapperClass']);
        }

        if (! empty($connection['use_savepoints'])) {
            $def->addMethodCall('setNestTransactionsWithSavepoints', [$connection['use_savepoints']]);
        }

        // Create a shard_manager for this connection
        if (isset($options['shards'])) {
            $shardManagerDefinition = new Definition($options['shardManagerClass'], [new Reference(sprintf('doctrine.dbal.%s_connection', $name))]);
            $container->setDefinition(sprintf('doctrine.dbal.%s_shard_manager', $name), $shardManagerDefinition);
        }

        // dbal < 2.11 BC layer
        if (! interface_exists(ConnectionProvider::class)) {
            return;
        }

        $container->setDefinition(
            ManagerRegistryAwareConnectionProvider::class,
            new Definition(ManagerRegistryAwareConnectionProvider::class, [$container->getDefinition('doctrine')])
        );
    }

    protected function getConnectionOptions($connection)
    {
        $options = $connection;

        if (isset($options['platform_service'])) {
            $options['platform'] = new Reference($options['platform_service']);
            unset($options['platform_service']);
        }

        unset($options['mapping_types']);

        if (isset($options['shard_choser_service'])) {
            $options['shard_choser'] = new Reference($options['shard_choser_service']);
            unset($options['shard_choser_service']);
        }

        foreach (
            [
                'options' => 'driverOptions',
                'driver_class' => 'driverClass',
                'wrapper_class' => 'wrapperClass',
                'keep_slave' => class_exists(PrimaryReadReplicaConnection::class) ? 'keepReplica' : 'keepSlave',
                'keep_replica' => 'keepReplica',
                'replicas' => 'replica',
                'shard_choser' => 'shardChoser',
                'shard_manager_class' => 'shardManagerClass',
                'server_version' => 'serverVersion',
                'default_table_options' => 'defaultTableOptions',
            ] as $old => $new
        ) {
            if (! isset($options[$old])) {
                continue;
            }

            $options[$new] = $options[$old];
            unset($options[$old]);
        }

        if (! empty($options['slaves']) && ! empty($options['replica']) && ! empty($options['shards'])) {
            throw new InvalidArgumentException('Sharding and master-slave connection cannot be used together');
        }

        if (! empty($options['slaves']) || ! empty($options['replica'])) {
            $nonRewrittenKeys = [
                'driver' => true,
                'driverOptions' => true,
                'driverClass' => true,
                'wrapperClass' => true,
                'keepSlave' => true,
                'keepReplica' => true,
                'shardChoser' => true,
                'platform' => true,
                'slaves' => true,
                'master' => true,
                'primary' => true,
                'replica' => true,
                'shards' => true,
                'serverVersion' => true,
                'defaultTableOptions' => true,
                // included by safety but should have been unset already
                'logging' => true,
                'profiling' => true,
                'mapping_types' => true,
                'platform_service' => true,
            ];
            foreach ($options as $key => $value) {
                if (isset($nonRewrittenKeys[$key])) {
                    continue;
                }

                $options[class_exists(PrimaryReadReplicaConnection::class) ? 'primary' : 'master'][$key] = $value;
                unset($options[$key]);
            }

            if (empty($options['wrapperClass'])) {
                // Change the wrapper class only if user did not configure custom one.
                $options['wrapperClass'] = class_exists(PrimaryReadReplicaConnection::class) ?
                    PrimaryReadReplicaConnection::class : // dbal >= 2.11
                    MasterSlaveConnection::class; // dbal < 2.11
            }
        } else {
            unset($options['slaves'], $options['replica']);
        }

        if (! empty($options['shards'])) {
            $nonRewrittenKeys = [
                'driver' => true,
                'driverOptions' => true,
                'driverClass' => true,
                'wrapperClass' => true,
                'keepSlave' => true,
                'keepReplica' => true,
                'shardChoser' => true,
                'platform' => true,
                'slaves' => true,
                'replica' => true,
                'global' => true,
                'shards' => true,
                'serverVersion' => true,
                'defaultTableOptions' => true,
                // included by safety but should have been unset already
                'logging' => true,
                'profiling' => true,
                'mapping_types' => true,
                'platform_service' => true,
            ];
            foreach ($options as $key => $value) {
                if (isset($nonRewrittenKeys[$key])) {
                    continue;
                }

                $options['global'][$key] = $value;
                unset($options[$key]);
            }

            if (empty($options['wrapperClass'])) {
                // Change the wrapper class only if the user does not already forced using a custom one.
                $options['wrapperClass'] = 'Doctrine\\DBAL\\Sharding\\PoolingShardConnection';
            }

            if (empty($options['shardManagerClass'])) {
                // Change the shard manager class only if the user does not already forced using a custom one.
                $options['shardManagerClass'] = 'Doctrine\\DBAL\\Sharding\\PoolingShardManager';
            }
        } else {
            unset($options['shards']);
        }

        return $options;
    }
}