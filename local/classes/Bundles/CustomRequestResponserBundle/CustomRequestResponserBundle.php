<?php declare(strict_types=1);

namespace Local\Bundles\CustomRequestResponserBundle;

use Local\Bundles\CustomRequestResponserBundle\DependencyInjection\CustomRequestResponserExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class CustomRequestResponserBundle
 * @package Local\Bundles\CustomRequestResponserBundle
 *
 * @since 04.12.2020
 */
class CustomRequestResponserBundle extends Bundle
{
    /**
     * @return CustomRequestResponserExtension
     */
    public function getContainerExtension(): CustomRequestResponserExtension
    {
        return new CustomRequestResponserExtension;
    }
}
