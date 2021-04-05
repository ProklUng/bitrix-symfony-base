<?php

namespace Local\Bundles\SymfonyBladeBundle\Services;

use Illuminate\View\FileViewFinder;

/**
 * Class ViewFinder
 * @package Local\Bundles\SymfonyBladeBundle\Services
 */
class ViewFinder extends FileViewFinder
{
    /**
     * Setter for paths.
     *
     * @param array $paths Пути.
     *
     * @return $this
     */
    public function setPaths($paths) : self
    {
        $this->paths = $paths;

        return $this;
    }
}
