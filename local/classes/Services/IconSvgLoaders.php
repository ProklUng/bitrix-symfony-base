<?php

namespace Local\Services;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;

/**
 * Class IconSvgLoaders
 * Загрузчик иконок SVG.
 * @package Local\Services
 *
 * @since 07.09.2020
 */
class IconSvgLoaders
{
    /**
     * @var Filesystem $filesystem Файловая система.
     */
    private $filesystem;

    /**
     * @var string $iconPath Путь к файлу с иконками.
     */
    private $iconPath;

    /**
     * @var string $buildPath Путь к сборке.
     */
    private $buildPath;

    /**
     * IconSvgLoaders constructor.
     *
     * @param Filesystem $filesystem Файловая система.
     * @param string     $buildPath  Путь к сборке.
     * @param string     $iconPath   Путь к файлу с иконками.
     */
    public function __construct(
        Filesystem $filesystem,
        string $buildPath,
        string $iconPath
    ) {
        $this->filesystem = $filesystem;
        $this->iconPath = $iconPath;
        $this->buildPath = $buildPath;
    }

    /**
     * Загрузить.
     *
     * @return string
     * @throws FileNotFoundException Файл не найден.
     */
    public function load() : string
    {
        $content = $this->filesystem->read(
            '/' . $this->buildPath . $this->iconPath
        );

        if (!$content) {
            $content = '';
        }

        return $content;
    }
}
