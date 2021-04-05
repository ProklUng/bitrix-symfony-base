<?php

namespace Local\Bundles\SymfonyBladeBundle\Services\Providers;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\Compilers\BladeCompiler;
use Local\Bundles\SymfonyBladeBundle\Services\BladeProcessors\BladeBase;
use RuntimeException;

/**
 * Class BladeBaseProvider
 * @package Local\Bundles\SymfonyBladeBundle\Services\Providers
 *
 * @since 08.08.2021
 */
class BladeBaseProvider
{
    /**
     * Path to a folder view common view can be stored.
     *
     * @var string
     */
    protected static $baseViewPath;

    /**
     * Local path to blade cache storage.
     *
     * @var string
     */
    protected static $cachePath;

    /**
     * View factory.
     *
     * @var Factory $viewFactory
     */
    protected static $viewFactory;

    /**
     * Service container factory.
     *
     * @var Container $container
     */
    protected static $container;

    /**
     * Register.
     *
     * @param array $params Параметры.
     *
     * @return void
     */
    public static function register(array $params = []) : void
    {
        $baseViewPath = $params['baseViewPath'];
        $cachePath = $params['cachePath'];

        static::$baseViewPath = static::isAbsolutePath($baseViewPath) ? $baseViewPath : $_SERVER['DOCUMENT_ROOT'].'/'.$baseViewPath;
        static::$cachePath = static::isAbsolutePath($cachePath) ? $cachePath : $_SERVER['DOCUMENT_ROOT'].'/'.$cachePath;

        static::instantiateServiceContainer();
        static::instantiateViewFactory();
    }

    /**
     * @param string $path Путь.
     *
     * @return boolean
     */
    protected static function isAbsolutePath(string $path) : bool
    {
        return $path && ($path[0] === DIRECTORY_SEPARATOR || preg_match('~\A[A-Z]:(?![^/\\\\])~i', $path) > 0);
    }

    /**
     * Get view factory.
     *
     * @return Factory
     */
    public static function getViewFactory() : Factory
    {
        return static::$viewFactory;
    }

    /**
     * @return BladeCompiler
     */
    public static function getCompiler() : BladeCompiler
    {
        return static::$container['blade.compiler'];
    }

    /**
     * Путь к кэшу.
     *
     * @return string|null
     */
    public static function getCachePath(): ?string
    {
        return static::$cachePath;
    }

    /**
     * Clear all compiled view files.
     *
     * @return boolean
     * @throws RuntimeException
     */
    public static function clearCache() : bool
    {
        $path = static::$cachePath;

        if (!$path) {
            throw new RuntimeException('Cache path is empty');
        }

        $success = true;
        foreach (glob("{$path}/*") as $view) {
            if (!@unlink($view)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Update paths where blade tries to find additional views.
     *
     * @param string $templateDir Директория с шаблоном.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public static function addTemplateFolderToViewPaths(string $templateDir) : void
    {
        $finder = Container::getInstance()->make('view.finder');

        $currentPaths = $finder->getPaths();
        $newPaths = [$_SERVER['DOCUMENT_ROOT'].$templateDir];

        // Полностью перезаписывать пути нельзя, иначе вложенные компоненты + include перестанут работать.
        $newPaths = array_values(array_unique(array_merge($newPaths, $currentPaths)));
        if (!in_array(static::$baseViewPath, $newPaths, true)) {
            $newPaths[] = static::$baseViewPath;
        }

        // Необходимо очистить внутренний кэш ViewFinder-а
        // Потому что иначе если в родительском компоненте есть @include('foo'), то при вызове @include('foo') из дочернего,
        // он не будет искать foo в дочернем, а сразу подключит foo из родительского компонента
        $finder->flush();

        $finder->setPaths($newPaths);
    }

    /**
     * Undo addTemplateFolderToViewPaths.
     *
     * @param string $templateDir Директория с шаблонами.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public static function removeTemplateFolderFromViewPaths(string $templateDir) : void
    {
        $finder = Container::getInstance()->make('view.finder');
        $currentPaths = $finder->getPaths();

        $finder->setPaths(
            array_diff($currentPaths, [$_SERVER['DOCUMENT_ROOT'].$templateDir] )
        );

        // Необходимо очистить внутренний кэш ViewFinder-а
        // Потому что иначе если в дочернем компоненте есть @include('foo'), то при вызове @include('foo') в родительском
        // после подключения дочернего,
        // он не будет искать foo в родительском, а сразу подключит foo из дочернего компонента
        $finder->flush();
    }

    /**
     * Instantiate service container if it's not instantiated yet.
     *
     * @return void
     */
    protected static function instantiateServiceContainer() : void
    {
        $container = Container::getInstance();

        if (!$container) {
            $container = new Container();
            Container::setInstance($container);
        }

        static::$container = $container;
    }

    /**
     * Instantiate view factory.
     *
     * @return void
     */
    protected static function instantiateViewFactory() : void
    {
        static::createDirIfNotExist(static::$baseViewPath);
        static::createDirIfNotExist(static::$cachePath);

        $viewPaths = [
            static::$baseViewPath,
        ];

        $cache = static::$cachePath;

        $blade = new BladeBase($viewPaths, $cache, static::$container);

        static::$viewFactory = $blade->view();
        static::$viewFactory->addExtension('blade', 'blade');
    }

    /**
     * Create dir if it does not exist.
     *
     * @param string $path Путь.
     *
     * @return void
     */
    protected static function createDirIfNotExist(string $path) : void
    {
        if (!file_exists($path)) {
            $mask = umask(0);
            mkdir($path, 0777, true);
            umask($mask);
        }
    }
}
