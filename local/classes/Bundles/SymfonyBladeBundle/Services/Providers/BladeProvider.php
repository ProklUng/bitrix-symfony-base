<?php

namespace Local\Bundles\SymfonyBladeBundle\Services\Providers;

use Illuminate\Container\Container;
use Illuminate\Contracts\View\Factory;
use Local\Bundles\SymfonyBladeBundle\Services\BladeCompilerBitrix;
use Local\Bundles\SymfonyBladeBundle\Services\BladeProcessors\BladeBitrix;

require_once $_SERVER['DOCUMENT_ROOT'].'/local/classes/Bundles/SymfonyBladeBundle/Services/functions/functions.php';

/**
 * Class BladeProvider
 * @package Local\Bundles\SymfonyBladeBundle\Services\Providers
 */
class BladeProvider extends BladeBaseProvider
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
     * Register blade engine in Bitrix.
     *
     * @param array $params Параметры.
     *
     * @return void
     */
    public static function register(array $params = []) : void
    {
        $baseViewPath = $params['baseViewPath'] ?: 'local/views';
        $cachePath = $params['cachePath'] ?: 'bitrix/cache/blade';

        static::$baseViewPath = static::isAbsolutePath($baseViewPath) ? $baseViewPath : $_SERVER['DOCUMENT_ROOT'].'/'.$baseViewPath;
        static::$cachePath = static::isAbsolutePath($cachePath) ? $cachePath : $_SERVER['DOCUMENT_ROOT'].'/'.$cachePath;

        static::instantiateServiceContainer();
        static::instantiateViewFactory();
        static::registerBitrixDirectives();

        global $arCustomTemplateEngines;
        $arCustomTemplateEngines['blade'] = [
            'templateExt' => ['blade'],
            'function'    => 'renderBladeTemplate',
        ];
    }

    /**
     * @return string
     */
    public static function getClass() : string
    {
        return static::class;
    }

    /**
     * @return BladeCompilerBitrix
     */
    public static function getCompiler() : BladeCompilerBitrix
    {
        return static::$container['blade.compiler.bitrix'];
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

        $blade = new BladeBitrix($viewPaths, $cache, static::$container);

        static::$viewFactory = $blade->view();
        static::$viewFactory->addExtension('blade', 'blade');
    }

    /**
     * Register bitrix directives.
     *
     * @return void
     */
    protected static function registerBitrixDirectives(): void
    {
        $compiler = static::getCompiler();

        $endIf = function () {
            return '<?php endif; ?>';
        };

        $compiler->directive('bxComponent', function ($expression) {
            $expression = rtrim($expression, ')');
            $expression = ltrim($expression, '(');

            return '<?php $APPLICATION->IncludeComponent('.$expression.'); ?>';
        });

        $compiler->directive('block', function ($expression) {
            $expression = rtrim($expression, ')');
            $expression = ltrim($expression, '(');

            return '<?php ob_start(); $__bx_block = ' . $expression . '; ?>';
        });

        $compiler->directive('endblock', function () {
            return '<?php $APPLICATION->AddViewContent($__bx_block, ob_get_clean()); ?>';
        });

        $compiler->directive('lang', function ($expression) {
            return '<?= Bitrix\Main\Localization\Loc::getMessage('.$expression.') ?>';
        });

        $compiler->directive('auth', function () {
            return '<?php if($USER->IsAuthorized()): ?>';
        });
        $compiler->directive('guest', function () {
            return '<?php if(!$USER->IsAuthorized()): ?>';
        });
        $compiler->directive('admin', function () {
            return '<?php if($USER->IsAdmin()): ?>';
        });
        $compiler->directive('csrf', function ($name = 'sessid') {
            $name = !empty($name) ? $name : 'sessid';
            $name = trim($name, '"');
            $name = trim($name, "'");
            return '<input type="hidden" name="'.$name.'" value="<?= bitrix_sessid() ?>" />';
        });

        $compiler->directive('endauth', $endIf);
        $compiler->directive('endguest', $endIf);
        $compiler->directive('endadmin', $endIf);

        static::registerHermitageDirectives($compiler);
    }

    /**
     * @param BladeCompilerBitrix $compiler Компилер.
     *
     * @return void
     */
    private static function registerHermitageDirectives(BladeCompilerBitrix $compiler): void
    {
        $simpleDirectives = [
            'actionAddForIBlock' => 'addForIBlock',
        ];
        foreach ($simpleDirectives as $directive => $action) {
            $compiler->directive($directive, function ($expression) use ($action) {
                $expression = rtrim($expression, ')');
                $expression = ltrim($expression, '(');
                return '<?php \Arrilot\BitrixHermitage\Action::' . $action . '($template, ' . $expression . '); ?>';
            });
        }

        $echoDirectives = [
            'actionEditIBlockElement' => 'editIBlockElement',
            'actionDeleteIBlockElement' => 'deleteIBlockElement',
            'actionEditAndDeleteIBlockElement' => 'editAndDeleteIBlockElement',

            'actionEditIBlockSection' => 'editIBlockSection',
            'actionDeleteIBlockSection' => 'deleteIBlockSection',
            'actionEditAndDeleteIBlockSection' => 'editAndDeleteIBlockSection',

            'actionEditHLBlockElement' => 'editHLBlockElement',
            'actionDeleteHLBlockElement' => 'deleteHLBlockElement',
            'actionEditAndDeleteHLBlockElement' => 'editAndDeleteHLBlockElement',
        ];

        foreach ($echoDirectives as $directive => $action) {
            $compiler->directive($directive, function ($expression) use ($action) {
                $expression = rtrim($expression, ')');
                $expression = ltrim($expression, '(');
                return '<?= \Arrilot\BitrixHermitage\Action::' . $action . '($template, ' . $expression . '); ?>';
            });
        }
    }
}
