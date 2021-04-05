<?php

namespace Local\Services\Twig\Extensions;

use Exception;
use Local\Util\Assets;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig_ExtensionInterface;

/**
 * Class SymfonyEncoreExtension
 * @package Local\Services\Twig\Extensions
 *
 * @since 22.10.2020
 */
class SymfonyEncoreExtension extends AbstractExtension implements Twig_ExtensionInterface
{
    /**
     * @var Assets $assetsService Сервис работы с ассетами.
     */
    protected $assetsService;

    /**
     * SymfonyEncore constructor.
     *
     * @param Assets $assetsService Сервис работы с ассетами.
     */
    public function __construct(
        Assets $assetsService
    ) {
        $this->assetsService = $assetsService;
    }

    /**
     * Return extension name
     *
     * @return string
     */
    public function getName()
    {
        return 'encore_extension';
    }

    /**
     * {@inheritdoc}
     */
    /**
     * Twig functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('encore_entry_link_tags', [$this, 'entryLinkTag']),
            new TwigFunction('encore_entry_script_tags', [$this, 'entryLinkScript']),
        ];
    }

    /**
     * encore_entry_link_tags().
     *
     * @param string $entry         Точка входа.
     * @param bool   $addPreloadTag Добавлять link="preload"?
     *
     * @return void
     */
    public function entryLinkTag(string $entry, bool $addPreloadTag = false): void
    {
        $link = $this->getLinkEntry($entry);
        if (!$link) {
            return;
        }

        $finalLink = '<link rel="stylesheet" href="' . $link . '">';

        if ($addPreloadTag) {
            $finalLink = '<link rel="preload" as="style" href="' . $link . '">' . $finalLink;
        }

        echo $finalLink;
    }

    /**
     * encore_entry_script_tags().
     *
     * @param string $entry Точка входа.
     *
     * @return void
     */
    public function entryLinkScript(string $entry): void
    {
        $link = $this->getLinkEntry($entry);
        if (!$link) {
            return;
        }

        echo '<script src="' . $link . '"></script>';
    }

    /**
     * Получить ссылку на ассет из манифеста.
     *
     * @param string $entry Точка входа.
     *
     * @return string
     */
    public function getLinkEntry(string $entry): string
    {
        try {
            return $this->assetsService->getEntry($entry);
        } catch (Exception $e) {
            return '';
        }
    }
}
