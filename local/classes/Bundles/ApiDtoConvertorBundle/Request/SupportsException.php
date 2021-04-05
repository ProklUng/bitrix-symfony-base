<?php

namespace Local\Bundles\ApiDtoConvertorBundle\Request;

use LogicException;

/**
 * Class SupportsException
 * @package Local\Bundles\ApiDtoConvertorBundle\Request
 *
 * @since 04.11.2020
 */
class SupportsException extends LogicException
{
    /**
     * @return $this
     */
    public static function covered(): self
    {
        return new self('This should have been covered by self::supports(). This is a bug, please report.');
    }
}
