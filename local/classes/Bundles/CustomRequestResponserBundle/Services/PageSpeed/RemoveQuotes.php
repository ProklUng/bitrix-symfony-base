<?php

namespace Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed;

/**
 * Class RemoveQuotes
 * @package Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed
 *
 * Eliminates unnecessary quotation marks from HTML attributes.
 *
 * @since 18.02.2021
 */
class RemoveQuotes extends AbstractPageSpeed
{
    /**
     * @inheritDoc
     */
    public function apply(string $buffer) : string
    {
        $replace = [
            '/ src="(.\S*?)"/' => ' src=$1',
            '/ width="(.\S*?)"/' => ' width=$1',
            '/ height="(.\S*?)"/' => ' height=$1',
            '/ name="(.\S*?)"/' => ' name=$1',
            '/ charset="(.\S*?)"/' => ' charset=$1',
            '/ align="(.\S*?)"/' => ' align=$1',
            '/ border="(.\S*?)"/' => ' border=$1',
            '/ crossorigin="(.\S*?)"/' => ' crossorigin=$1',
            '/ type="(.\S*?)"/' => ' type=$1',
            '/\/>/' => '>',
        ];

        return $this->replace($replace, $buffer);
    }
}
