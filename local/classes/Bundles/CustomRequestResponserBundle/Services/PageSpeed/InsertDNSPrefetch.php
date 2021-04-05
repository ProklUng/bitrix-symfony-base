<?php

namespace Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed;

/**
 * Class InsertDNSPrefetch
 * Injects tags in the HEAD to enable the browser to do DNS prefetching.
 * @package Local\Bundles\CustomRequestResponserBundle\Services\PageSpeed
 *
 * @since 18.02.2021
 */
class InsertDNSPrefetch extends AbstractPageSpeed
{
    /**
     * @inheritDoc
     */
    public function apply(string $buffer): string
    {
        preg_match_all(
            '#\bhttps?://[^\s()<>]+(?:\([\w]+\)|([^[:punct:]\s]|/))#',
            $buffer,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        $dnsPrefetch = collect($matches[0])->map(function ($item) {
            $domain = (new TrimUrl)->apply($item[0]);
            $domain = explode(
                '/',
                str_replace('//', '', $domain)
            );

            return "<link rel=\"dns-prefetch\" href=\"//{$domain[0]}\">";
        })->unique()->implode("\n");

        $replace = [
            '#<head>(.*?)#' => "<head>\n{$dnsPrefetch}"
        ];

        return $this->replace($replace, $buffer);
    }
}
