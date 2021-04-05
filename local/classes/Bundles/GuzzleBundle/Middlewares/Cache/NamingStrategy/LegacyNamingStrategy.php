<?php

namespace Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy;

use Psr\Http\Message\RequestInterface;

/**
 * @deprecated The LegacyNamingStrategy is deprecated since version 2.1, and will be removed in 3.0
 */
class LegacyNamingStrategy extends AbstractNamingStrategy
{
    /**
     * @var boolean $withHost
     */
    private $withHost;

    /**
     * @param boolean  $withHost
     * @param array    $blacklist
     */
    public function __construct($withHost, array $blacklist = [])
    {
        $this->withHost = $withHost;

        parent::__construct($blacklist);
    }

    /**
     * {@inheritdoc}
     */
    public function filename(RequestInterface $request) : string
    {
        if ($this->withHost) {
            return $this->sanitize(call_user_func_array(
                'sprintf',
                array_merge(['%s_%s_%s-%s____%s'], $this->getPartsWithHost($request))
            ));
        }

        return $this->sanitize(call_user_func_array(
            'sprintf',
            array_merge(['%s_%s-%s____%s'], $this->getPartsWithoutHost($request))
        ));
    }

    /**
     * @param RequestInterface $request
     *
     * @return array
     */
    private function getPartsWithHost(RequestInterface $request): array
    {
        return [
            str_pad($request->getMethod(), 6, '_'),
            $request->getUri()->getHost(),
            urldecode(ltrim($request->getUri()->getPath(), '/')),
            urldecode($request->getUri()->getQuery()),
            $this->getFingerprint($request),
        ];
    }

    /**
     * @param RequestInterface $request
     *
     * @return array
     */
    private function getPartsWithoutHost(RequestInterface $request): array
    {
        return [
            str_pad($request->getMethod(), 6, '_'),
            urldecode(ltrim($request->getUri()->getPath(), '/')),
            urldecode($request->getUri()->getQuery()),
            $this->getFingerprint($request),
        ];
    }

    /**
     * Sanitizes a filename.
     *
     * @param string $filename
     *
     * @return string
     */
    private function sanitize($filename): string
    {
        return preg_replace('/[^a-zA-Z0-9_+=@\-\?\.]/', '-', $filename);
    }
}
