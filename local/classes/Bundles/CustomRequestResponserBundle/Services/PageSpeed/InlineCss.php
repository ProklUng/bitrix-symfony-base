<?php

namespace Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed;

/**
 * Class InlineCss
 * @package Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed
 *
 * Transforms the inline "style" attribute of tags into classes by moving the CSS to the header.
 *
 * @since 18.02.2021
 */
class InlineCss extends AbstractPageSpeed
{
    /**
     * @var string $html
     */
    private $html = '';

    /**
     * @var array $class
     */
    private $class = [];

    /**
     * @var array $style
     */
    private $style = [];

    /**
     * @var array $inline
     */
    private $inline = [];

    /**
     * @inheritDoc
     */
    public function apply(string $buffer) : string
    {
        $this->html = $buffer;

        preg_match_all(
            '#style="(.*?)"#',
            $this->html,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        $this->class = collect($matches[1])->mapWithKeys(function ($item) {

            return [ 'page_speed_'.mt_rand() => $item[0] ];
        })->unique();

        return $this->injectStyle()->injectClass()->fixHTML()->html;
    }

    /**
     * @return InlineCss
     */
    private function injectStyle(): InlineCss
    {
        collect($this->class)->each(function ($attributes, $class) {

            $this->inline[] = ".{$class}{ {$attributes} }";

            $this->style[] = [
                'class' => $class,
                'attributes' => preg_quote($attributes, '/')];
        });

        $injectStyle = implode(' ', $this->inline);

        $replace = [
            '#</head>(.*?)#' => "\n<style>{$injectStyle}</style>\n</head>"
        ];

        $this->html = $this->replace($replace, $this->html);

        return $this;
    }

    /**
     * @return InlineCss
     */
    private function injectClass(): InlineCss
    {
        collect($this->style)->each(function ($item) {
            $replace = [
                '/style="'.$item['attributes'].'"/' => "class=\"{$item['class']}\"",
            ];

            $this->html = $this->replace($replace, $this->html);
        });

        return $this;
    }

    /**
     * @return InlineCss
     */
    private function fixHTML(): InlineCss
    {
        $newHTML = [];
        $tmp = explode('<', $this->html);

        $replaceClass = [
            '/class="(.*?)"/' => "",
        ];

        foreach ($tmp as $value) {
            preg_match_all('/class="(.*?)"/', $value, $matches);

            if (count($matches[1]) > 1) {
                $replace = [
                    '/>/' => "class=\"".implode(' ', $matches[1])."\">",
                ];

                $newHTML[] = str_replace(
                    '  ',
                    ' ',
                    $this->replace($replace, $this->replace($replaceClass, $value))
                );
            } else {
                $newHTML[] = $value;
            }
        }

        $this->html = implode('<', $newHTML);

        return $this;
    }
}
