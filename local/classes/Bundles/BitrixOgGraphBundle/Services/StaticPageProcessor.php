<?php

namespace Local\Bundles\BitrixOgGraphBundle\Services;

use Bitrix\Main\Application;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class StaticPageProcessor
 * @package Local\Bundles\BitrixOgGraphBundle\Services
 *
 * @since 19.02.2021
 */
class StaticPageProcessor extends AbstractProcessor
{
    /**
     * @var string $documentRoot
     */
    private $documentRoot;

    /**
     * @var Application $application Битриксовый Application.
     */
    private $application;

    /**
     * @var CacheInterface $cacher Кэшер.
     */
    private $cacher;

    /**
     * StaticPageProcessor constructor.
     *
     * @param string         $documentRoot DOCUMENT_ROOT.
     * @param Application    $application  Битриксовый Application.
     * @param CacheInterface $cacher       Кэшер.
     */
    public function __construct(
        string $documentRoot,
        Application $application,
        CacheInterface $cacher
    ) {
        $this->documentRoot = $documentRoot;
        $this->application = $application;
        $this->cacher = $cacher;
    }

    /**
     * Движуха.
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function go() : array
    {
        $request = $this->application->getContext()->getRequest();
        $uri = strtok($request->getRequestUri(), '?');

        $key = md5('og_static' . SITE_ID . $uri);

        return $this->cacher->get($key, function (ItemInterface $item) use ($uri) {
            return $this->query($uri);
        });
    }

    /**
     * @param string $uri URL.
     *
     * @return array
     */
    private function query(string $uri) : array
    {
        $timestamp = @filemtime($this->documentRoot . $uri . '/index.php');

        $result = [
            'title' => $GLOBALS['APPLICATION']->GetPageProperty('title') ?: '',
            'description' => $this->cutDescription(
                $GLOBALS['APPLICATION']->GetPageProperty('description') ?: ''
            ),
            'type' => 'website',
            'timePublished' => $timestamp ? date('Y-m-d H:i:s', $timestamp) : '',
            'url' => $this->getFullUrl($uri)
        ];

        if ($GLOBALS['APPLICATION']->GetPageProperty('og:image')) {
            $result['img'] = $this->getFullUrl(
                $GLOBALS['APPLICATION']->GetPageProperty('og:image')
            );
        }

        return $result;
    }
}
