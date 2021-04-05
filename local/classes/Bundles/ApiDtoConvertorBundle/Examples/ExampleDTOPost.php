<?php

namespace Local\Bundles\ApiDtoConvertorBundle\Examples;

use Local\Bundles\ApiDtoConvertorBundle\DependencyInjection\BaseDTOInterface;

/**
 * Class ExampleDTO
 * @package Local\Bundles\ApiDtoConvertorBundle\Examples
 * @Local\Bundles\ApiDtoConvertorBundle\HttpApi\HttpApiPost
 *
 * @since 04.11.2020
 */
class ExampleDTOPost implements BaseDTOInterface
{
    public $amount;
    public $articles;
}
