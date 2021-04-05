<?php

namespace Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed;

/**
 * Class TrimUrl
 * @package Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed
 *
 * @since 18.02.2021
 */
class TrimUrl extends AbstractPageSpeed
{
    public function apply(string $buffer) : string
    {
        $replace = [
            '/https:/' => '',
            '/http:/' => ''
        ];

        return $this->replace($replace, $buffer);
    }
}
