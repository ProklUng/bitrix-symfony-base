<?php

declare(strict_types=1);

namespace Local\Bundles\UrlSignedBundle\UrlSigner;

use DateTime;
use Spatie\UrlSigner\BaseUrlSigner;
use Spatie\UrlSigner\Exceptions\InvalidExpiration;
use Spatie\UrlSigner\Exceptions\InvalidSignatureKey;

/**
 * Class AbstractUrlSigner
 * @package Local\Bundles\UrlSignedBundle\UrlSigner
 */
abstract class AbstractUrlSigner extends BaseUrlSigner implements UrlSignerInterface
{
    /**
     * @var integer $defaultExpiration
     */
    private $defaultExpiration;

    /**
     * AbstractUrlSigner constructor.
     *
     * @param string  $signatureKey
     * @param integer $defaultExpiration
     * @param string  $expiresParameter
     * @param string  $signatureParameter
     *
     * @throws InvalidSignatureKey
     */
    public function __construct(string $signatureKey, int $defaultExpiration, string $expiresParameter, string $signatureParameter)
    {
        parent::__construct($signatureKey, $expiresParameter, $signatureParameter);

        $this->defaultExpiration = $defaultExpiration;
    }

    /**
     * @param string                $url        URL.
     * @param DateTime|integer|null $expiration
     *
     * @return string
     * @throws InvalidExpiration
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function sign($url, $expiration = null): string
    {
        return parent::sign($url, $expiration ?? $this->defaultExpiration);
    }
}
