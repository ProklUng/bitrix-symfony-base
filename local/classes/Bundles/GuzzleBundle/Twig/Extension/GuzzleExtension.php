<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Local\Bundles\GuzzleBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Csa Guzzle Collector.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class GuzzleExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters() : array
    {
        return [
            new TwigFilter('csa_guzzle_pretty_print', [$this, 'prettyPrint']),
            new TwigFilter('csa_guzzle_status_code_class', [$this, 'statusCodeClass']),
            new TwigFilter('csa_guzzle_format_duration', [$this, 'formatDuration']),
            new TwigFilter('csa_guzzle_short_uri', [$this, 'shortenUri']),
        ];
    }

    /**
     * Get functions.
     *
     * @return TwigFunction[]
     */
    public function getFunctions() : array
    {
        return [
            new TwigFunction('csa_guzzle_detect_lang', [$this, 'detectLang']),
        ];
    }

    /**
     * @param string $body
     *
     * @return string
     */
    public function detectLang(string $body) : string
    {
        switch (true) {
            case 0 === strpos($body, '<?xml'):
                return 'xml';
            case 0 === strpos($body, '{'):
            case 0 === strpos($body, '['):
                return 'json';
            default:
                return 'markup';
        }
    }

    /**
     * Pretty print.
     *
     * @param string $code
     * @param string $lang
     *
     * @return false|string
     */
    public function prettyPrint(string $code, string $lang)
    {
        switch ($lang) {
            case 'json':
                return json_encode(json_decode($code), JSON_PRETTY_PRINT);
            case 'xml':
                $xml = new \DomDocument('1.0');
                $xml->preserveWhiteSpace = false;
                $xml->formatOutput = true;
                $xml->loadXML($code, LIBXML_NOWARNING);

                return $xml->saveXML();
            default:
                return $code;
        }
    }

    /**
     * @param integer $statusCode
     *
     * @return string
     */
    public function statusCodeClass(int $statusCode) : string
    {
        switch (true) {
            case $statusCode >= 500:
                return 'server-error';
            case $statusCode >= 400:
                return 'client-error';
            case $statusCode >= 300:
                return 'redirection';
            case $statusCode >= 200:
                return 'success';
            case $statusCode >= 100:
                return 'informational';
            default:
                return 'unknown';
        }
    }

    /**
     * @param mixed $seconds
     *
     * @return string
     */
    public function formatDuration($seconds) : string
    {
        $formats = ['%.2f s', '%d ms', '%d µs'];

        while ($format = array_shift($formats)) {
            if ($seconds > 1) {
                break;
            }

            $seconds *= 1000;
        }

        return sprintf($format, $seconds);
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    public function shortenUri(string $uri) : string
    {
        $parts = parse_url($uri);

        return sprintf(
            '%s://%s%s',
            $parts['scheme'] ?? 'http',
            $parts['host'],
            // @phpstan-ignore-next-line
            isset($parts['port']) ? (':'.$parts['port']) : ''
        );
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'csa_guzzle';
    }
}
