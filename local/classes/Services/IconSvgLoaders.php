<?php

namespace Local\Services;

use RuntimeException;

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
     * @var string $iconPath Путь к файлу с иконками.
     */
    private $iconPath;

    /**
     * @var string $buildPath Путь к сборке.
     */
    private $buildPath;

    /**
     * @var string $documentRoot DOCUMENT_ROOT.
     */
    private $documentRoot;

    /**
     * IconSvgLoaders constructor.
     *
     * @param string $documentRoot DOCUMENT_ROOT.
     * @param string $buildPath    Путь к сборке.
     * @param string $iconPath     Путь к файлу с иконками.
     */
    public function __construct(
        string $documentRoot,
        string $buildPath,
        string $iconPath
    ) {
        $this->iconPath = $iconPath;
        $this->buildPath = $buildPath;
        $this->documentRoot = $documentRoot;
    }

    /**
     * Загрузить.
     *
     * @return string
     * @throws RuntimeException Файл не найден.
     */
    public function load() : string
    {
        $content = file_get_contents(
            $this->documentRoot . '/' . $this->buildPath . $this->iconPath
        );

        if ($content === false) {
            throw new RuntimeException(
                'File with icons ' .  $this->iconPath . ' not found'
            );
        }

        return $content;
    }
}