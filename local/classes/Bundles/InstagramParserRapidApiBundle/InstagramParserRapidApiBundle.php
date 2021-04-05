<?php

namespace Local\Bundles\InstagramParserRapidApiBundle;

use Local\Bundles\InstagramParserRapidApiBundle\DependencyInjection\InstagramParserRapidApiExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class InstagramParserRapidApiBundle
 * @package Local\Bundles\InstagramParserRapidApiBundle
 *
 * @since 22.02.2021
 */
class InstagramParserRapidApiBundle extends Bundle
{
   /**
   * @inheritDoc
   */
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new InstagramParserRapidApiExtension();
        }

        return $this->extension;
    }
}
