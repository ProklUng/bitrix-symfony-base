<?php

namespace Local\ServiceProvider;

use Bitrix\Main\Application;
use CMain;
use Exception;
use InvalidArgumentException;
use Local\ServiceProvider\Bundles\BundlesLoader;
use Local\ServiceProvider\Extra\ExtraFeature;
use Local\ServiceProvider\Framework\SymfonyCompilerPassBag;
use Local\Services\AppKernel;
use Local\Util\ErrorScreen;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\ObjectInitializerInterface;

/**
 * Class ServiceProvider
 * @package Local\ServiceProvider
 *
 * @since 11.09.2020 Подключение возможности обработки событий HtppKernel через Yaml конфиг.
 * @since 21.09.2020 Исправление ошибки: сервисы, помеченные к автозагрузке не запускались в
 * случае компилированного контейнера.
 * @since 28.09.2020 Доработка.
 * @since 24.10.2020 Загрузка "автономных" бандлов Symfony.
 * @since 08.11.2020 Устранение ошибки, связанной с многократной загрузкой конфигурации бандлов.
 * @since 12.11.2020 Значение debug передаются снаружи. Рефакторинг.
 * @since 14.11.2020 Загрузка конфигураций бандлов.
 * @since 12.12.2020 Полноценный контейнер в kernel.
 * @since 12.12.2020 DoctrineDbalExtension.
 * @since 21.12.2020 Нативная поддержка нативных аннотированных роутов.
 * @since 03.03.2021 Разные компилированные контейнеры в зависмости от файла конфигурации.
 * @since 20.03.2021 Поддержка разных форматов (Yaml, php, xml) конфигурации контейнера. Удаление ExtraFeature
 * внутрь соответствующего класса.
 * @since 04.04.2021 Вынес стандартные compile pass Symfony в отдельный класс.
 * @since 14.04.2021 Метод boot бандлов вызывается теперь после компиляции контейнера.
 * @since 27.04.2021 Баг-фикс: при скомпилированном контейнере не запускался метод boot бандлов.
 */
class ServiceProvider
{
    /**
     * @const string SERVICE_CONFIG_FILE Конфигурация сервисов.
     */
    private const SERVICE_CONFIG_FILE = 'local/configs/services.yaml';

    /**
     * @const string COMPILED_CONTAINER_PATH Файл с сскомпилированным контейнером.
     */
    private const COMPILED_CONTAINER_FILE = '/container.php';

    /**
     * @const string COMPILED_CONTAINER_DIR Путь к скомпилированному контейнеру.
     */
    private const COMPILED_CONTAINER_DIR = '/bitrix/cache/s1';

    /**
     * @const string CONFIG_EXTS Расширения конфигурационных файлов.
     */
    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /**
     * @var ContainerBuilder $containerBuilder Контейнер.
     */
    protected static $containerBuilder;

    /**
     * @var string $pathBundlesConfig Путь к конфигурации бандлов.
     */
    protected $pathBundlesConfig = '/local/configs/standalone_bundles.php';

    /**
     * @var string $configDir Папка, где лежат конфиги.
     */
    protected $configDir = '/local/configs';

    /**
     * @var ErrorScreen $errorHandler Обработчик ошибок.
     */
    private $errorHandler;

    /**
     * @var Filesystem $filesystem Файловая система.
     */
    private $filesystem;

    /**
     * @var BundlesLoader $bundlesLoader Загрузчик бандлов.
     */
    private $bundlesLoader;

    /**
     * @var string $filename Файл конфигурации контейнера.
     */
    private $filename;

    /**
     * @var string $projectRoot DOCUMENT_ROOT.
     */
    private $projectRoot = '';

    /**
     * @var array Конфигурация бандлов.
     */
    private $bundles = [];

    /**
     * @var array $compilerPassesBag Набор Compiler Pass.
     */
    private $compilerPassesBag = [];

    /**
     * @var string[] $postLoadingPassesBag Пост-обработчики (PostLoadingPass) контейнера.
     */
    private $postLoadingPassesBag = [];

    /**
     * @var string $environment Среда.
     */
    private $environment;

    /**
     * @var boolean $debug Режим dev?
     */
    private $debug;

    /**
     * @var array $standartCompilerPasses Пассы Symfony.
     */
    protected $standartCompilerPasses = [];

    /**
     * @var string $symfonyCompilerClass Класс с симфоническими compiler passes.
     */
    protected $symfonyCompilerClass = SymfonyCompilerPassBag::class;

    /**
     * ServiceProvider constructor.
     *
     * @param string $filename Конфиг.
     *
     * @throws Exception Ошибка инициализации контейнера.
     */
    public function __construct(
        string $filename = self::SERVICE_CONFIG_FILE
    ) {
        // Buggy local fix.
        $_ENV['DEBUG'] = env('DEBUG', false);
        $this->environment = $_ENV['DEBUG'] ? 'dev' : 'prod';
        $this->debug = (bool)$_ENV['DEBUG'];

        $this->errorHandler = new ErrorScreen(
            new CMain()
        );

        $this->filesystem = new Filesystem();

        if (!$filename) {
            $filename = self::SERVICE_CONFIG_FILE;
        }

        $this->filename = $filename;

        if (static::$containerBuilder !== null) {
            return;
        }

        $frameworkCompilePasses = new $this->symfonyCompilerClass;
        $this->standartCompilerPasses = $frameworkCompilePasses->getStandartCompilerPasses();

        // Кастомные Compile pass & PostLoadingPass.
        $customCompilePassesBag = new CustomCompilePassBag();

        $this->compilerPassesBag = $customCompilePassesBag->getCompilerPassesBag();
        $this->postLoadingPassesBag = $customCompilePassesBag->getPostLoadingPassesBag();

        $this->projectRoot = Application::getDocumentRoot();

        $result = $this->initContainer($filename);
        if (!$result) {
            $this->errorHandler->die('Container DI inititalization error.');
            throw new Exception('Container DI inititalization error.');
        }
    }

    /**
     * Сервис по ключу.
     *
     * @param string $id ID сервиса.
     *
     * @return mixed
     * @throws Exception Ошибки контейнера.
     */
    public function get(string $id)
    {
        return static::$containerBuilder->get($id);
    }

    /**
     * Контейнер.
     *
     * @return ContainerBuilder
     */
    public function container(): ContainerBuilder
    {
        return static::$containerBuilder ?: $this->initContainer($this->filename);
    }

    /**
     * Жестко установить контейнер.
     *
     * @param PsrContainerInterface $container Контейнер.
     *
     * @return void
     */
    public function setContainer(PsrContainerInterface $container): void
    {
        static::$containerBuilder  = $container;
    }

    /**
     * Инициализировать контейнер.
     *
     * @param string $fileName Конфиг.
     *
     * @return mixed
     *
     * @since 28.09.2020 Доработка.
     */
    private function initContainer(string $fileName)
    {
        // Если в dev режиме, то не компилировать контейнер.
        if (env('DEBUG', false) === true) {
            if (static::$containerBuilder !== null) {
                return static::$containerBuilder;
            }

            // Загрузить, инициализировать и скомпилировать контейнер.
            static::$containerBuilder = $this->initialize($fileName);

            // Исполнить PostLoadingPasses.
            $this->runPostLoadingPasses();

            return static::$containerBuilder;
        }

        // Создать директорию
        // для компилированного контейнера.
        $this->createCacheDirectory();

        /** Путь к скомпилированному контейнеру. */
        $compiledContainerFile = $this->getPathCacheDirectory($this->filename)
            . self::COMPILED_CONTAINER_FILE;

        $containerConfigCache = new ConfigCache($compiledContainerFile, true);
        // Класс скомпилированного контейнера.
        $classCompiledContainerName = $this->getContainerClass() . md5($this->filename);

        if (!$containerConfigCache->isFresh()) {
            // Загрузить, инициализировать и скомпилировать контейнер.
            static::$containerBuilder = $this->initialize($fileName);

            // Блокировка на предмет конкурентных запросов.
            $lockFile = $this->getPathCacheDirectory($this->filename) . '/container.lock';

            // Silence E_WARNING to ignore "include" failures - don't use "@" to prevent silencing fatal errors
            $errorLevel = error_reporting(\E_ALL ^ \E_WARNING);

            $lock = false;
            try {
                if ($lock = fopen($lockFile, 'w')) {
                    flock($lock, \LOCK_EX | \LOCK_NB, $wouldBlock);
                    if (!flock($lock, $wouldBlock ? \LOCK_SH : \LOCK_EX)) {
                        fclose($lock);
                        @unlink($lockFile);
                        $lock = null;
                    }
                } else {
                    // Если в файл контейнера уже что-то пишется, то вернем свежую копию контейнера.
                    flock($lock, \LOCK_UN);
                    fclose($lock);
                    @unlink($lockFile);

                    // Исполнить PostLoadingPasses.
                    $this->runPostLoadingPasses();

                    return static::$containerBuilder;
                }

            } catch (\Throwable $e) {
            } finally {
                error_reporting($errorLevel);
            }

            $this->dumpContainer($containerConfigCache, static::$containerBuilder, $classCompiledContainerName);

            if ($lock) {
                flock($lock, \LOCK_UN);
                fclose($lock);
                @unlink($lockFile);
            }
        }

        // Подключение скомпилированного контейнера.
        require_once $compiledContainerFile;

        $classCompiledContainerName = '\\'.$classCompiledContainerName;

        static::$containerBuilder = new $classCompiledContainerName();

        // Boot bundles.
        BundlesLoader::bootAfterCompilingContainer(static::$containerBuilder);

        // Исполнить PostLoadingPasses.
        $this->runPostLoadingPasses();

        return static::$containerBuilder;
    }

    /**
     * Dumps the service container to PHP code in the cache.
     *
     * @param ConfigCache      $cache     Кэш.
     * @param ContainerBuilder $container Контейнер.
     * @param string           $class     The name of the class to generate.
     *
     * @return void
     *
     * @since 20.03.2021 Форк оригинального метода с приближением к реальности.
     */
    private function dumpContainer(ConfigCache $cache, ContainerBuilder $container, string $class) : void
    {
        // Опция в конфиге - компилировать ли контейнер.
        if ($container->hasParameter('compile.container')
            &&
            !$container->getParameter('compile.container')) {
            return;
        }

        // Опция - дампить как файлы. По умолчанию - нет.
        $asFiles = false;
        if ($container->hasParameter('container.dumper.inline_factories')) {
            $asFiles = $container->getParameter('container.dumper.inline_factories');
        }

        $dumper = new PhpDumper(static::$containerBuilder);
        if (class_exists(\ProxyManager\Configuration::class) && class_exists(ProxyDumper::class)) {
            $dumper->setProxyDumper(new ProxyDumper());
        }

        $content = $dumper->dump(
            [
                'class' => $class,
                'file' => $cache->getPath(),
                'as_files' => $asFiles,
                'debug' => $this->debug,
                'build_time' => static::$containerBuilder->hasParameter('kernel.container_build_time')
                    ? static::$containerBuilder->getParameter('kernel.container_build_time') : time(),
                'preload_classes' => array_map('get_class', $this->bundles),
            ]
        );

        // Если as_files = true.
        if (is_array($content)) {
            $rootCode = array_pop($content);
            $dir = \dirname($cache->getPath()).'/';

            foreach ($content as $file => $code) {
                $this->filesystem->dumpFile($dir.$file, $code);
                @chmod($dir.$file, 0666 & ~umask());
            }

            $legacyFile = \dirname($dir.key($content)).'.legacy';
            if (is_file($legacyFile)) {
                @unlink($legacyFile);
            }

            $content = $rootCode;
        }

        $cache->write(
            $content, // @phpstan-ignore-line
            static::$containerBuilder->getResources()
        );
    }

    /**
     * Gets the container class.
     *
     * @return string The container class.
     * @throws InvalidArgumentException If the generated classname is invalid.
     */
    private function getContainerClass() : string
    {
        $class = static::class;
        $class = false !== strpos($class, "@anonymous\0") ? get_parent_class($class).str_replace('.', '_', ContainerBuilder::hash($class))
            : $class;
        $class = str_replace('\\', '_', $class).ucfirst($this->environment).($this->debug ? 'Debug' : '').'Container';

        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $class)) {
            throw new InvalidArgumentException(sprintf('The environment "%s" contains invalid characters, it can only contain characters allowed in PHP class names.', $this->environment));
        }

        return $class;
    }

    /**
     * Загрузить контейнер.
     *
     * @param string $fileName Конфиг.
     *
     * @return boolean|ContainerBuilder
     *
     * @throws Exception Ошибки контейнера.
     *
     * @since 28.09.2020 Набор стандартных Compile Pass. Кастомные Compiler Pass.
     * @since 11.09.2020 Подключение возможности обработки событий HtppKernel через Yaml конфиг.
     */
    private function loadContainer(string $fileName)
    {
        static::$containerBuilder = new ContainerBuilder();
        // Если изменился этот файл, то перестроить контейнер.
        static::$containerBuilder->addObjectResource($this);

        $this->setDefaultParamsContainer();

        // Инициализация автономных бандлов.
        $this->loadSymfonyBundles();

        // Набор стандартных Compile Pass
        $passes = new PassConfig();
        $allPasses = $passes->getPasses();

        foreach ($allPasses as $pass) {
            // Тонкость: MergeExtensionConfigurationPass добавляется в BundlesLoader.
            // Если не проигнорировать здесь, то он вызовется еще раз.
            if (get_class($pass) === MergeExtensionConfigurationPass::class) {
                continue;
            }
            static::$containerBuilder->addCompilerPass($pass);
        }

        $this->standartSymfonyPasses();

        // Локальные compile pass.
        foreach ($this->compilerPassesBag as $compilerPass) {
            $passInitiated = !empty($compilerPass['params']) ? new $compilerPass['pass'](...$compilerPass['params'])
                :
                new $compilerPass['pass'];

            // Фаза. По умолчанию PassConfig::TYPE_BEFORE_OPTIMIZATION
            $phase = !empty($compilerPass['phase']) ? $compilerPass['phase'] : PassConfig::TYPE_BEFORE_OPTIMIZATION;

            static::$containerBuilder->addCompilerPass($passInitiated, $phase);
        }

        // Подключение возможности обработки событий HtppKernel через Yaml конфиг.
        // tags:
        //      - { name: kernel.event_listener, event: kernel.request, method: handle }
        static::$containerBuilder->register('event_dispatcher', EventDispatcher::class);

        $registerListenersPass = new RegisterListenersPass();
        $registerListenersPass->setHotPathEvents([
            KernelEvents::REQUEST,
            KernelEvents::CONTROLLER,
            KernelEvents::CONTROLLER_ARGUMENTS,
            KernelEvents::RESPONSE,
            KernelEvents::FINISH_REQUEST,
        ]);

        static::$containerBuilder->addCompilerPass($registerListenersPass);

        try {
            // Загрузка основного конфига контейнера.
            if (!$this->loadContainerConfig($fileName, static::$containerBuilder)) {
                return false;
            }

            // Подгрузить конфигурации из папки config.
            $this->configureContainer(
                static::$containerBuilder,
                $this->getContainerLoader(static::$containerBuilder)
            );

            // FrameworkExtension.
            $this->registerFrameworkExtensions();

            // Контейнер в AppKernel, чтобы соответствовать Symfony.
            if (static::$containerBuilder->has('kernel')) {
                $kernelService = static::$containerBuilder->get('kernel');
                if ($kernelService) {
                    $kernelService->setContainer(static::$containerBuilder);
                }
            }

            return static::$containerBuilder;
        } catch (Exception $e) {
            $this->errorHandler->die('Ошибка загрузки Symfony Container: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Загрузить, инициализировать и скомпилировать контейнер.
     *
     * @param string $fileName Конфигурационный файл.
     *
     * @return null|ContainerBuilder
     *
     * @since 28.09.2020
     */
    private function initialize(string $fileName): ?ContainerBuilder
    {
        try {
            $this->loadContainer($fileName);

            // Дополнить переменные приложения сведениями о зарегистрированных бандлах.
            static::$containerBuilder->get('kernel')->registerStandaloneBundles();

            static::$containerBuilder->getParameterBag()->add(
                static::$containerBuilder->get('kernel')->getKernelParameters()
            );

            $this->bundlesLoader->registerExtensions(static::$containerBuilder);

            static::$containerBuilder->compile(true);

            // Boot bundles.
            $this->bundlesLoader->boot(static::$containerBuilder);
        } catch (Exception $e) {
            $this->errorHandler->die(
                $e->getMessage().'<br><br><pre>'.$e->getTraceAsString().'</pre>'
            );

            return null;
        }

        return static::$containerBuilder;
    }

    /**
     * Параметры контейнера и регистрация сервиса kernel.
     *
     * @return void
     *
     * @throws Exception Ошибки контейнера.
     *
     * @since 12.11.2020 Полная переработка. Регистрация сервиса.
     */
    private function setDefaultParamsContainer() : void
    {
        if (!static::$containerBuilder->hasDefinition('kernel')) {
            static::$containerBuilder->register('kernel', AppKernel::class)
                ->addTag('service.bootstrap')
                ->setAutoconfigured(true)
                ->setPublic(true)
                ->setArguments([$_ENV['DEBUG']])
            ;
        }

        static::$containerBuilder->getParameterBag()->add(
            static::$containerBuilder->get('kernel')->getKernelParameters()
        );
    }

    /**
     * Если надо создать директорию для компилированного контейнера.
     *
     * @return void
     */
    private function createCacheDirectory() : void
    {
        $dir = $this->getPathCacheDirectory($this->filename);

        if (!$this->filesystem->exists($dir)) {
            try {
                $this->filesystem->mkdir($dir);
            } catch (IOExceptionInterface $exception) {
                $this->errorHandler->die('An error occurred while creating your directory at '.$exception->getPath());
            }
        }
    }

    /**
     * Путь к директории с компилированным контейнером.
     *
     * @param string $filename Конфигурация.
     *
     * @return string
     *
     * @since 03.03.2021
     */
    private function getPathCacheDirectory(string $filename) : string
    {
        return $this->projectRoot . self::COMPILED_CONTAINER_DIR .'/containers/'. md5($filename);
    }

    /**
     * Стандартные Symfony манипуляции над контейнером.
     *
     * @return void
     *
     * @since 28.09.2020
     *
     * @see FrameworkBundle
     */
    private function standartSymfonyPasses(): void
    {
        /** @var array $autoConfigure Автоконфигурация тэгов. */
        /** @var array $autoConfigure Автоконфигурация тэгов. */
        $autoConfigure = [
            'controller.service_arguments' => AbstractController::class,
            'controller.argument_value_resolver' => ArgumentValueResolverInterface::class,
            'container.service_locator' => ServiceLocator::class,
            'kernel.event_subscriber' => EventSubscriberInterface::class,
            'validator.constraint_validator' => ConstraintValidatorInterface::class,
            'validator.initializer' => ObjectInitializerInterface::class,
        ];

        foreach ($autoConfigure as $tag => $class) {
            static::$containerBuilder->registerForAutoconfiguration($class)
                ->addTag($tag);
        }

        // Применяем compiler passes.
        foreach ($this->standartCompilerPasses as $pass) {
            if (!array_key_exists('pass', $pass) || !class_exists($pass['pass'])) {
                continue;
            }
            static::$containerBuilder->addCompilerPass(
                new $pass['pass'],
                $pass['phase'] ?? PassConfig::TYPE_BEFORE_OPTIMIZATION
            );
        }
    }

    /**
     * Загрузка "автономных" бандлов Symfony.
     *
     * @return void
     *
     * @throws InvalidArgumentException  Не найден класс бандла.
     *
     * @since 24.10.2020
     */
    private function loadSymfonyBundles() : void
    {
        $this->bundlesLoader = new BundlesLoader(
            static::$containerBuilder,
            $this->pathBundlesConfig
        );

        $this->bundlesLoader->load(); // Загрузить бандлы.

        $this->bundles = $this->bundlesLoader->bundles();
    }

    /**
     * Запустить PostLoadingPasses.
     *
     * @return void
     *
     * @since 26.09.2020
     * @since 21.03.2021 Маркер, что пасс уже запускался.
     */
    private function runPostLoadingPasses(): void
    {
        /**
         * Отсортировать по приоритету.
         *
         * @psalm-suppress MissingClosureParamType
         * @psalm-suppress InvalidScalarArgument
         */
        usort($this->postLoadingPassesBag, static function ($a, $b) : bool {
            // @phpstan-ignore-line
            return $a['priority'] > $b['priority'];
        });

        // Запуск.
        foreach ($this->postLoadingPassesBag as $key => $postLoadingPass) {
            if (class_exists($postLoadingPass['pass']) && !array_key_exists('runned', $postLoadingPass)) {
                $class = new $postLoadingPass['pass'];
                $class->action(static::$containerBuilder);

                // Отметить, что пасс уже запускался.
                $this->postLoadingPassesBag[$key]['runned'] = true;
            }
        }
    }

    /**
     * Загрузка конфигурационного файла контейнера.
     *
     * @param string           $fileName         Конфигурационный файл.
     * @param ContainerBuilder $containerBuilder Контейнер.
     *
     * @return boolean
     * @throws Exception
     *
     * @since 20.03.2021
     */
    private function loadContainerConfig(string $fileName, ContainerBuilder $containerBuilder) : bool
    {
        $loader = $this->getContainerLoader($containerBuilder);

        try {
            $loader->load($_SERVER['DOCUMENT_ROOT'] . '/' . $fileName);
            return true;
        } catch (Exception $e) {
            $this->errorHandler->die('Сервис-контейнер: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Загрузка конфигураций в различных форматах из папки configs.
     *
     * @param ContainerBuilder $container Контейнер.
     * @param LoaderInterface  $loader    Загрузчик.
     *
     * @return void
     * @throws Exception Ошибки контейнера.
     *
     * @since 06.11.2020
     */
    private function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $confDir = $_SERVER['DOCUMENT_ROOT'] . $this->configDir;
        $container->setParameter('container.dumper.inline_class_loader', true);

        try {
            $loader->load($confDir.'/packages/*'.self::CONFIG_EXTS, 'glob');
        } catch (Exception $e) {
        }

        if (is_dir($confDir . '/packages/' . $this->environment)) {
            $loader->load($confDir . '/packages/' . $this->environment . '/**/*' . self::CONFIG_EXTS, 'glob');
        }

        $loader->load($confDir . '/services_'. $this->environment. self::CONFIG_EXTS, 'glob');
    }

    /**
     * Returns a loader for the container.
     *
     * @param ContainerBuilder $container Контейнер.
     *
     * @return DelegatingLoader The loader
     * @throws Exception Ошибки контейнера.
     *
     * @since 06.11.2020
     */
    private function getContainerLoader(ContainerBuilder $container): DelegatingLoader
    {
        $locator = new \Symfony\Component\HttpKernel\Config\FileLocator(
            static::$containerBuilder->get('kernel')
        );

        $resolver = new LoaderResolver([
            new XmlFileLoader($container, $locator),
            new YamlFileLoader($container, $locator),
            new IniFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
            new ClosureLoader($container),
        ]);

        return new DelegatingLoader($resolver);
    }

    /**
     * Регистрация Framework Extensions.
     *
     * @return void
     *
     * @since 28.11.2020
     * @since 21.12.2020 Нативная поддержка нативных аннотированных роутов.
     *
     * @throws Exception
     */
    protected function registerFrameworkExtensions() : void
    {
        $frameworkExtension = new ExtraFeature();
        $frameworkExtension->register(static::$containerBuilder);
    }

    /**
     * Статический фасад получение контейнера.
     *
     * @param string $method Метод. В данном случае instance().
     * @param mixed  $args   Аргументы (конфигурационный файл).
     *
     * @return mixed | void
     * @throws Exception Ошибки контейнера.
     */
    public static function __callStatic(string $method, $args = null)
    {
        if ($method === 'instance') {
            if (!empty(static::$containerBuilder)) {
                return static::$containerBuilder;
            }

            $self = new static(...$args);

            return $self->container();
        }
    }
}
