<?php

namespace Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy;

use Psr\Http\Message\RequestInterface;

/**
 * Class SubfolderNamingStrategy
 * @package Local\Bundles\GuzzleBundle\Middlewares\Cache\NamingStrategy
 */
class SubfolderNamingStrategy extends AbstractNamingStrategy
{
    /**
     * {@inheritdoc}
     */
    public function filename(RequestInterface $request): string
    {
        $filename = $request->getUri()->getHost();

        if ('' !== $path = urldecode(ltrim($request->getUri()->getPath(), '/'))) {
            $filename .= '/'.$path;
        }

        $filename .= '/'.$request->getMethod();
        $filename .= '_'.$this->getFingerprint($request);

        return $filename;
    }
}
