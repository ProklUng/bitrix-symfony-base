<?php

namespace Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed;

/**
 * Class RemoveComments
 * Eliminates HTML, JS and CSS comments. The filter reduces the transfer size of HTML files by
 * removing the comments. Depending on the HTML file, this filter can significantly reduce the number of bytes transmitted on the network.
 * @package Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed
 *
 * @since 18.02.2021
 *
 */
class RemoveComments extends AbstractPageSpeed
{
    private const REGEX_MATCH_JS_AND_CSS_COMMENTS = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/';
    private const REGEX_MATCH_HTML_COMMENTS = '/<!--[^]><!\[](.*?)[^\]]-->/s';

    /**
     * @inheritDoc
     */
    public function apply(string $buffer) : string
    {
        $buffer = $this->replaceInsideHtmlTags(['script', 'style'], self::REGEX_MATCH_JS_AND_CSS_COMMENTS, '', $buffer);

        $replaceHtmlRules = [
            self::REGEX_MATCH_HTML_COMMENTS => '',
        ];

        return $this->replace($replaceHtmlRules, $buffer);
    }
}
