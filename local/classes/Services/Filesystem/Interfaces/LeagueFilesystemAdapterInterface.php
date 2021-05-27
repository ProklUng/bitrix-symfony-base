<?php

namespace Local\Services\Filesystem\Interfaces;

use League\Flysystem\AdapterInterface;

/**
 * Interface LeagueFilesystemAdapterInterface
 * @package Local\Services\Interfaces
 */
interface LeagueFilesystemAdapterInterface
{
    /**
     * Адаптер.
     *
     * @return AdapterInterface
     */
    public function getAdapter(): AdapterInterface;
}
