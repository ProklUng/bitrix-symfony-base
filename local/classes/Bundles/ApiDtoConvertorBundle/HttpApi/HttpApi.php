<?php

declare(strict_types=1);

namespace Local\Bundles\ApiDtoConvertorBundle\HttpApi;

/**
 * @Annotation
 * @Target("CLASS")
 */
class HttpApi
{
    public const BODY = 'attribute';

    public const POST = 'post';
    public const ATTRIBUTE = 'attribute';
    public const QUERY_STRING = 'query_string';

    /**
     * @var string
     */
    public $requestInfoSource = self::ATTRIBUTE;
}
