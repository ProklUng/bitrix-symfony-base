<?php

namespace Local\Bundles\StaticPageMakerBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig_ExtensionInterface;
use Bitrix\Main\Page\Asset;

/**
 * Class AssetsExtension
 * @package Local\Bundles\StaticPageMakerBundle\Twig
 *
 * @since 23.01.2021
 */
class AssetsExtension extends AbstractExtension implements Twig_ExtensionInterface
{
    /**
     * Return extension name
     *
     * @return string
     */
    public function getName()
    {
        return 'static_page_maker.assets_handler_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() : array
    {
        return [
            new TwigFunction('add_css', [$this, 'addCss']),
            new TwigFunction('add_js', [$this, 'addJs']),
            new TwigFunction('add_string', [$this, 'addString']),
        ];
    }

    /**
     * Добавить CSS.
     *
     * @param string $path Путь.
     *
     * @return void
     */
    public function addCss(string $path) : void
    {
        Asset::getInstance()->addCss($path);
    }

    /**
     * Добавить JS.
     *
     * @param string $path Путь.
     *
     * @return void
     */
    public function addJs(string $path) : void
    {
        Asset::getInstance()->addJs($path);
    }

    /**
     * Добавить строку в header.
     *
     * @param string $value Строка.
     *
     * @return void
     */
    public function addString(string $value) : void
    {
        Asset::getInstance()->addString($value);
    }
}