<?php

namespace Local\Services\Filesystem\Interfaces;

use League\Flysystem\AdapterInterface;

/**
 * Interface FilesystemAdapterInterface
 * @package Local\Services\Interfaces
 */
interface FilesystemAdapterInterface
{
    /**
     * @return AdapterInterface
     */
    public function getAdapter(): AdapterInterface;
}
