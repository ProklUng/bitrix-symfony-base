<?php

namespace Local\Bundles\ApiDtoConvertorBundle\Examples;

use Local\Bundles\ApiDtoConvertorBundle\DependencyInjection\BaseDTOInterface;

/**
 * Class ExampleDTO
 * @package Local\Bundles\ApiDtoConvertorBundle\Examples
 * @Local\Bundles\ApiDtoConvertorBundle\HttpApi\HttpApi
 */
class ExampleDTO implements BaseDTOInterface
{
    public $amount;
    public $articles;
}
