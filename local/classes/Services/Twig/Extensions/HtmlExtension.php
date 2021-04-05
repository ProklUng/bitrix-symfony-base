<?php

namespace Local\Services\Twig\Extensions;

use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Extension\ExtensionInterface;

/**
 * Class HtmlExtension
 * Html extension.
 * @package Local\Services\Twig\Extensions
 *
 * @since 31.10.2020
 *
 * @see https://twig.symfony.com/doc/3.x/functions/html_classes.html
 */
class HtmlExtension extends AbstractExtension implements ExtensionInterface
{
    /**
     * Return extension name.
     *
     * @return string
     */
    public function getName()
    {
        return 'twig/html-extension';
    }

    /**
     * Функции.
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('html_classes', [$this, 'twig_html_classes']),
        ];
    }

    /**
     * @param mixed ...$args
     *
     * @return string
     * @throws RuntimeError
     */
    public function twig_html_classes(...$args): string
    {
        $classes = [];
        foreach ($args as $i => $arg) {
            if (\is_string($arg)) {
                $classes[] = $arg;
            } elseif (\is_array($arg)) {
                foreach ($arg as $class => $condition) {
                    if (!\is_string($class)) {
                        throw new RuntimeError(sprintf('The html_classes function argument %d (key %d) should be a string, got "%s".', $i, $class, \gettype($class)));
                    }
                    if (!$condition) {
                        continue;
                    }
                    $classes[] = $class;
                }
            } else {
                throw new RuntimeError(sprintf('The html_classes function argument %d should be either a string or an array, got "%s".', $i, \gettype($arg)));
            }
        }

        return implode(' ', array_unique($classes));
    }
}
