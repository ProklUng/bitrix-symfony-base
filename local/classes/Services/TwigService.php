<?php

namespace Local\Services;

use Twig\Error\LoaderError;
use Twig_Environment;
use Twig_Loader_Filesystem;

/**
 * Class TwigService
 * @package Local\Services
 *
 * @since 07.09.2020
 * @since 12.10.2020 Доработка. Расширение функционала.
 */
class TwigService
{
    /**
     * @var Twig_Environment
     */
    private $twigEnvironment;

    /**
     * @var Twig_Loader_Filesystem $loader
     */
    private $loader;

    /** @var string $debug */
    private $debug;
    /** @var string $cachePath */
    private $cachePath;

    /**
     * TwigService constructor.
     *
     * @param Twig_Loader_Filesystem $loader    Загрузчик.
     * @param string                 $debug     Среда.
     * @param string                 $cachePath Путь к кэшу (серверный).
     */
    public function __construct(
        Twig_Loader_Filesystem $loader,
        string $debug,
        string $cachePath
    ) {
        $this->loader = $loader;
        $this->debug = $debug;
        $this->cachePath = $cachePath;

        $this->twigEnvironment = $this->initTwig(
            $loader,
            $debug,
            $cachePath
        );
    }

    /**
     * Инстанс Твига.
     *
     * @return Twig_Environment
     */
    public function instance() : Twig_Environment
    {
        return $this->twigEnvironment;
    }

    /**
     * Еще один базовый путь к шаблонам Twig.
     *
     * @param string $path Путь.
     *
     * @throws LoaderError
     */
    public function addPath(string $path)
    {
        $this->loader->addPath($path);

        // Переинициализировать.
        $this->twigEnvironment = $this->initTwig(
            $this->loader,
            $this->debug,
            $this->cachePath
        );
    }

    /**
     * Инициализация.
     *
     * @param Twig_Loader_Filesystem $loader    Загрузчик.
     * @param string                 $debug     Среда.
     * @param string                 $cachePath Путь к кэшу (серверный).
     *
     * @return Twig_Environment
     */
    protected function initTwig(
        Twig_Loader_Filesystem $loader,
        string $debug,
        string $cachePath
    ) : Twig_Environment {

        return new Twig_Environment(
            $loader,
            [
                'debug' => (bool)$debug,
                'cache' => $cachePath,
            ]
        );
    }
}
