<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Local\Bundles\GuzzleBundle;

use Local\Bundles\GuzzleBundle\DependencyInjection\CompilerPass\LoaderPass;
use Local\Bundles\GuzzleBundle\DependencyInjection\CompilerPass\MiddlewarePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Csa Guzzle Bundle.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class CsaGuzzleBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container) : void
    {
        parent::build($container);

        $container->addCompilerPass(new MiddlewarePass());
        $container->addCompilerPass(new LoaderPass());
    }
}
