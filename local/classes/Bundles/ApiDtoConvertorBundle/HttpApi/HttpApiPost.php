<?php

declare(strict_types=1);

namespace Local\Bundles\ApiDtoConvertorBundle\HttpApi;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * Указание - использовать POST параметры для формирования DTO.
 */
class HttpApiPost extends HttpApi
{
    /**
     * @var string
     */
    public $requestInfoSource = self::POST;
}
