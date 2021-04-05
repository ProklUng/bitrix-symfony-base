<?php

namespace Local\ServiceProvider\Bundles;

use InvalidArgumentException;
use Local\ServiceProvider\CompilePasses\MakePrivateCommandsPublic;
use Local\ServiceProvider\CompilePasses\MakePrivateEventsPublic;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;

/**
 * Class BundlesLoader
 * @package Local\ServiceProvider\Bundles
 * Загрузчик бандлов.
 *
 * @since 24.10.2020
 * @since 08.11.2020 Устранение ошибки, связанной с многократной загрузкой конфигурации бандлов.
 * @since 19.11.2020 Сделать все приватные подписчики событий публичными.
 * @since 20.12.2020 Сделать все приватные консольные команды публичными.
 * @since 04.03.2021 Возможность загрузки бандлов несколькими провайдерами.
 */
class BundlesLoader
{
    /** @const string PATH_BUNDLES_CONFIG Путь к конфигурационному файлу. */
    private const PATH_BUNDLES_CONFIG = '/local/configs/standalone_bundles.php';

    /**
     * @var ContainerBuilder $container Контейнер.
     */
    private $container;

    /**
     * @var array Конфигурация бандлов.
     */
    private $bundles = [];

    /**
     * @var array $bundlesMap Инициализированные классы бандлов.
     */
    private static $bundlesMap = [];

    /**
     * BundlesLoader constructor.
     *
     * @param ContainerBuilder $container  Контейнер в стадии формирования.
     * @param string           $configPath Путь к bundles.php (конфигурация бандлов).
     */
    public function __construct(
        ContainerBuilder $container,
        string $configPath = ''
    ) {
        $configPath = $configPath ?: self::PATH_BUNDLES_CONFIG;

        if (@file_exists($_SERVER['DOCUMENT_ROOT'] . $configPath)) {
            $this->bundles = require $_SERVER['DOCUMENT_ROOT'] . $configPath;
        }

        $this->container = $container;
        static::$bundlesMap[static::class] = [];
    }

    /**
     * Инициализация бандлов.
     *
     * @return void
     *
     * @throws InvalidArgumentException Не найден класс бандла.
     */
    public function load() : void
    {
        foreach ($this->bundles as $bundleClass => $envs) {
            if (!class_exists($bundleClass)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Bundle class %s not exist.',
                        $bundleClass
                    )
                );
            }

            /**
             * @var Bundle $bundle Бандл.
             */
            $bundle = new $bundleClass;

            if ((bool)$_ENV['DEBUG'] === true) {
                $this->container->addObjectResource($bundle);
            }

            $extension = $bundle->getContainerExtension();
            if ($extension !== null) {
                $this->container->registerExtension($extension);
                $bundle->build($this->container);

                // Сделать все приватные подписчики событий публичными.
                // Без этого они почему-то не подхватываются при загрузке бандлов.
                $this->container->addCompilerPass(
                    new MakePrivateEventsPublic()

                );

                // Сделать все приватные команды публичными.
                // Без этого они почему-то не подхватываются при загрузке бандлов.
                $this->container->addCompilerPass(
                    new MakePrivateCommandsPublic()
                );

                // Сохраняю инстанцированный бандл в статику.
                static::$bundlesMap[static::class][$bundle->getName()] = $bundle;
            } else {
                throw new InvalidArgumentException(
                    sprintf(
                        'Bundle %s dont have implemented getContainerExtension method.',
                        $bundle->getName()
                    )
                );
            }
        }
    }

    /**
     * Регистрация extensions.
     *
     * @param ContainerBuilder $container Контейнер.
     *
     * @return void
     */
    public function registerExtensions(ContainerBuilder $container) : void
    {
        // Extensions in container.
        $extensions = [];
        foreach ($container->getExtensions() as $extension) {
            $extensions[] = $extension->getAlias();
        }

        // ensure these extensions are implicitly loaded
        $container->getCompilerPassConfig()
            ->setMergePass(
                new MergeExtensionConfigurationPass($extensions)
            );
    }

    /**
     * Boot bundles.
     *
     * @param ContainerInterface $container Контейнер.
     *
     * @return void
     *
     * @since 11.11.2020
     */
    public function boot(ContainerInterface $container) : void
    {
        /**
         * @var Bundle $bundle
         */
        foreach (static::$bundlesMap[static::class] as $bundle) {
            $bundle->setContainer($container);
            $bundle->boot();
        }
    }

    /**
     * Бандлы.
     *
     * @return array
     */
    public function bundles() : array
    {
        return static::$bundlesMap[static::class] ?? [];
    }

    /**
     * Инстанцы бандлов.
     *
     * @return array
     */
    public static function getBundlesMap() : array
    {
        return static::$bundlesMap[static::class] ?? [];
    }
}
