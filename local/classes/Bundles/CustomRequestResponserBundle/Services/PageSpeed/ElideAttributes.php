<?php

namespace Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed;

/**
 * Class ElideAttributes
 * @package Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed
 *
 * Removing attributes from tags when the specified value is equal to the default value for that attribute.
 *
 * @since 18.02.2021
 */
class ElideAttributes extends AbstractPageSpeed
{
    /**
     * @inheritDoc
     */
    public function apply(string $buffer) : string
    {
        $replace = [
            '/ method=("get"|get)/' => '',
            '/ disabled=[^ >]*(.*?)/' => ' disabled',
            '/ selected=[^ >]*(.*?)/' => ' selected',
        ];

        return $this->replace($replace, $buffer);
    }
}
