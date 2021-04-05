<?php

namespace Local\Services\Twig\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class StringExtension
 * @package Local\Services\Twig\Extensions
 *
 * @since 09.03.2021
 */
class StringExtension extends AbstractExtension
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'twig/string-extension';
    }

    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('quote', [$this, 'quote']),
        ];
    }

    /**
     * @param string $value Значение.
     * @param string $quot  Кавычки.
     *
     * @return string
     */
    public function quote(string $value, string $quot = '\''): string
    {
        return "{$quot}$value{$quot}";
    }

}
