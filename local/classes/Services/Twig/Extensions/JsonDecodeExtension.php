<?php

namespace Local\Services\Twig\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig_ExtensionInterface;

/**
 * Class JsonDecodeExtension
 * json_decode in Twig.
 * @package Local\Services\Twig\Extensions
 *
 * @since 23.10.2020
 */
class JsonDecodeExtension extends AbstractExtension implements Twig_ExtensionInterface
{
    /**
     * Return extension name.
     *
     * @return string
     */
    public function getName()
    {
        return 'twig/json-decode';
    }

    /**
     * @inheritDoc
     *
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return array(
            new TwigFilter('json_decode', [$this, 'jsonDecode'])
        );
    }

    /**
     * Json_decode.
     *
     * @param string  $string     Строка.
     * @param boolean $assocArray В ассоциативный массив?
     *
     * @return mixed
     */
    public function jsonDecode(string $string, $assocArray = true)
    {
        return json_decode($string, $assocArray, 512);
    }
}