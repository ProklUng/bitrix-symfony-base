<?php

namespace Local\Bundles\ApiDtoConvertorBundle\Examples;

use Local\Bundles\ApiDtoConvertorBundle\DependencyInjection\BaseDTOInterface;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class ExampleDTO
 * @package Local\Bundles\ApiDtoConvertorBundle\Examples
 * DTO с валидацией.
 * @Local\Bundles\ApiDtoConvertorBundle\HttpApi\HttpApiPost
 */
class ExampleDTOPostSpatie extends DataTransferObject implements BaseDTOInterface
{
    /**
     * @var int $amount
     */
    public $amount;

    /**
     * @var string $articles
     */
    public $articles;

    /**
     * @var bool
     */
    public $unknown;

    /**
     * Правила валидации.
     *
     * @return array
     */
    public function getRules() : array
    {
        return [
            'amount' => 'numeric|min:1',
            'articles' => 'string|required',
        ];
    }

    /**
     * Правила санации.
     *
     * @return array
     */
    public static function getRulesSanitization() : array
    {
        return [
            'amount' => 'trim|escape|cast:integer',
            'articles' => 'trim|escape|cast:string',
            'unknown' => 'trim|escape|cast:boolean',
        ];
    }
}
