<?php

namespace Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed;

/**
 * Class CollapseWhitespace
 * @package Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed
 * Reduces bytes transmitted in an HTML file by removing unnecessary whitespace.
 *
 * @since 18.02.2021
 */
class CollapseWhitespace extends AbstractPageSpeed
{
    /**
     * @inheritDoc
     */
    public function apply(string $buffer) : string
    {
        $replace = [
            "/\n([\S])/" => '$1',
            "/\r/" => '',
            "/\n/" => '',
            "/\t/" => '',
            "/ +/" => ' ',
            "/> +</" => '><',
        ];

        return $this->replace($replace, $this->removeComments($buffer));
    }

    /**
     * @param string $buffer
     *
     * @return string
     */
    private function removeComments(string $buffer) : string
    {
        return (new RemoveComments)->apply($buffer);
    }
}
