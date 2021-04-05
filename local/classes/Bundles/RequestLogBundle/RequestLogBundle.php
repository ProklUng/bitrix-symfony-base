<?php

namespace Local\Bundles\RequestLogBundle;

use Local\Bundles\RequestLogBundle\DependencyInjection\RequestLogExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class RequestLogBundle
 * @package Local\Bundles\RequestLogBundle
 *
 * @since 06.03.2021
 */
final class RequestLogBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new RequestLogExtension();
        }

        return $this->extension;
    }
}
