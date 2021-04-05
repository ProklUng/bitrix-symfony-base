<?php

namespace Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed;

use Local\Bundles\CustomRequestResponserBundle\Services\Contracts\PageSpeedMiddlewareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractPageSpeed
 * @package Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed
 */
abstract class AbstractPageSpeed implements PageSpeedMiddlewareInterface
{
    /**
     * Apply rules.
     *
     * @param string $buffer Текстовый контент.
     *
     * @return string
     */
    abstract public function apply(string $buffer) : string;

    /**
     * Should Process?
     *
     * @param Request  $request  Request.
     * @param Response $response Response.
     *
     * @return boolean
     */
    public function shouldProcessPageSpeed(Request $request, Response $response) : bool
    {
        $typeRequest = $request->server->get('REQUEST_METHOD');
        if ($typeRequest !== 'GET' || !is_string($response->getContent())) {
            return false;
        }

        return true;
    }

    /**
     * Replace content response.
     *
     * @param array  $replace Замена.
     * @param string $buffer  Контент.
     *
     * @return string
     */
    protected function replace(array $replace, string $buffer) : string
    {
        return preg_replace(array_keys($replace), array_values($replace), $buffer);
    }

    /**
     * Match all occurrences of the html tags given
     *
     * @param array  $tags   Html tags to match in the given buffer.
     * @param string $buffer Middleware response buffer.
     *
     * @return array $matches Html tags found in the buffer
     */
    protected function matchAllHtmlTag(array $tags, string $buffer): array
    {
        $resultTags = '('.implode('|', $tags).')';

        preg_match_all("/\<\s*{$resultTags}[^>]*\>((.|\n)*?)\<\s*\/\s*{$resultTags}\>/", $buffer, $matches);
        return $matches;
    }

    /**
     * Replace occurrences of regex pattern inside of given HTML tags
     *
     * @param array  $tags    Html tags to match and run regex to replace occurrences.
     * @param string $regex   Regex rule to match on the given HTML tags.
     * @param string $replace Content to replace.
     * @param string $buffer  Middleware response buffer.
     *
     * @return string $buffer Middleware response buffer.
     */
    protected function replaceInsideHtmlTags(array $tags, string $regex, string $replace, string $buffer): string
    {
        foreach ($this->matchAllHtmlTag($tags, $buffer)[0] as $tagMatched) {
            preg_match_all($regex, $tagMatched, $tagContentsMatchedToReplace);

            foreach ($tagContentsMatchedToReplace[0] as $tagContentReplace) {
                $buffer = str_replace($tagContentReplace, $replace, $buffer);
            }
        }

        return $buffer;
    }
}
