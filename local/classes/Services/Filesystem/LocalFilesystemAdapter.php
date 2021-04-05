<?php

namespace Local\Services\Filesystem;

use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use Local\Services\Filesystem\Interfaces\FilesystemAdapterInterface;

/**
 * Class LocalFilesystemAdapter
 * @package Local\Services\Filesystem
 */
class LocalFilesystemAdapter implements FilesystemAdapterInterface
{
    /**
     * @var AdapterInterface $adapter Адаптер League файловой системы.
     */
    private $adapter;

    /**
     * LocalFilesystemAdapter constructor.
     *
     * @param string $rootDir DOCUMENT_ROOT.
     */
    public function __construct(
        string $rootDir
    ) {
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
     * @return AdapterInterface
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }
}
