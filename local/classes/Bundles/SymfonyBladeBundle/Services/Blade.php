<?php

namespace Local\Bundles\SymfonyBladeBundle\Services;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use RuntimeException;

/**
 * Class Blade
 * @package Local\Bundles\SymfonyBladeBundle\Services
 *
 * @since 08.03.2021
 */
class Blade
{
    /**
     * @var Factory $viewFactory View factory.
     */
    private $viewFactory;

    /**
     * @var array $baseViewDirs Пути, где ищется шаблон.
     */
    private $baseViewDirs;

    /**
     * Blade constructor.
     *
     * @param Factory $viewFactory  View factory.
     * @param array   $baseViewDirs Пути, где ищется шаблон.
     */
    public function __construct(
        Factory $viewFactory,
        array $baseViewDirs
    ) {
        $this->viewFactory = $viewFactory;
        $this->baseViewDirs = $baseViewDirs;
    }

    /**
     * Получить View шаблона.
     *
     * @param string $file      Blade шаблон.
     * @param array  $data      Данные.
     * @param array  $mergeData Данные.
     *
     * @return View
     */
    public function file(string $file, array $data = [], array $mergeData = []) : View
    {
        foreach ($this->baseViewDirs as $dir) {
            if (file_exists($dir . $file)) {
                return $this->viewFactory->file($dir . $file, $data, $mergeData);
            }
        }

        throw new RuntimeException(
            'View ' . $file . ' not found in base paths.'
        );
    }

    /**
     * Добавить базовый путь.
     *
     * @param string $path Базовый путь.
     *
     * @return void
     */
    public function addBasePath(string $path): void
    {
        $this->baseViewDirs[] = $path;
        $this->baseViewDirs = array_unique($this->baseViewDirs);
    }
}
