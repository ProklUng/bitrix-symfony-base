<?php

declare(strict_types=1);

namespace Local\Bundles\UrlSignedBundle\UrlSigner;

use Psr\Http\Message\UriInterface;

/**
 * Class Sha256UrlSigner
 * @package Local\Bundles\UrlSignedBundle\UrlSigner
 */
final class Sha256UrlSigner extends AbstractUrlSigner
{
    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'sha256';
    }

    /**
     * Generate a token to identify the secure action.
     *
     * @param UriInterface|string $url        URL.
     * @param string              $expiration
     *
     * @return string
     */
    protected function createSignature($url, string $expiration): string
    {
        return hash_hmac('sha256', "{$url}::{$expiration}", $this->signatureKey);
    }
}
