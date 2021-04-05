<?php

namespace Local\Bundles\BitrixComponentParamsBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

/**
 * Class NumericExtension
 * @package Local\Bundles\BitrixComponentParamsBundle\Twig
 *
 * @since 27.02.2021
 */
class NumericExtension extends AbstractExtension
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'twig/numeric-extension-bundle';
    }

    /**
     * @inheritDoc
     */
    public function getTests() : array
    {
        return [
            new TwigTest('numeric', function ($value) { return  is_numeric($value); }),
        ];
    }
}
