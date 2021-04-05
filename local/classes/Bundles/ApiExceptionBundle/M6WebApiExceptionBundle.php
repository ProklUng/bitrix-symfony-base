<?php

namespace Local\Bundles\ApiExceptionBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class bundle
 *
 * @see https://github.com/M6Web/ApiExceptionBundle/
 */
class M6WebApiExceptionBundle extends Bundle
{
    /**
     * @return DependencyInjection\M6WebApiExceptionExtension|null|ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new DependencyInjection\M6WebApiExceptionExtension();
    }
}
