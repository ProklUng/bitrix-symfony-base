<?php

namespace Local\Services\Filesystem;

use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use Local\Services\Filesystem\Interfaces\LeagueFilesystemAdapterInterface;

/**
 * Class LocalLeagueFilesystemAdapter
 * @package Local\Services\Filesystem
 */
class LocalLeagueFilesystemAdapter implements LeagueFilesystemAdapterInterface
{
    /**
     * @var AdapterInterface $adapter Адаптер League файловой системы.
     */
    private $adapter;

    /**
     * LocalLeagueFilesystemAdapter constructor.
     *
     * @param string $rootDir DOCUMENT_ROOT.
     */
    public function __construct(string $rootDir) {
        $this->adapter = new Local(
            $rootDir . '/',
            LOCK_EX,
            Local::DISALLOW_LINKS,
            [
                'file' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ]
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }
}
