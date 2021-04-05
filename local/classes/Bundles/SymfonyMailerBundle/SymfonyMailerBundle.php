<?php

namespace Local\Bundles\SymfonyMailerBundle;

use Local\Bundles\SymfonyMailerBundle\DependencyInjection\SymfonyMailerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SymfonyMailerBundle
 * @package Local\Bundles\SymfonyMailerBundle
 *
 * @since 02.03.2021
 */
class SymfonyMailerBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new SymfonyMailerExtension();
        }

        return $this->extension;
    }
}
