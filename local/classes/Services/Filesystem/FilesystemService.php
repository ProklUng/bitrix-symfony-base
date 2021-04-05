<?php

namespace Local\Services\Filesystem;

use League\Flysystem\Filesystem;
use Local\Services\Filesystem\Interfaces\FilesystemAdapterInterface;

/**
 * Class Filesystem
 * @package Local\Services
 */
class FilesystemService
{
    /**
     * @var Filesystem $filesystem Файловая система.
     */
    private $filesystem;

    /**
     * @var FilesystemService $backupFilesystem Резервная копия "файловой" системы.
     */
    private $backupFilesystem;

    /**
     * FilesystemService constructor.
     *
     * @param FilesystemAdapterInterface $adapter
     */
    public function __construct(
        FilesystemAdapterInterface $adapter
    ) {
        $this->setup($adapter);
    }

    /**
     * Инстанс файловой системы.
     *
     * @return Filesystem
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Сменить адаптер. С сохранением предыдущей системы в резервной копии.
     *
     * @param FilesystemAdapterInterface | null $adapter
     */
    public function swap(FilesystemAdapterInterface $adapter = null) : void
    {
        // Достать из резервной копии, если она есть.
        if ($adapter === null && $this->backupFilesystem !== null) {
            $this->filesystem = $this->backupFilesystem;
            return;
        }

        $this->backupFilesystem = $this->filesystem;
        $this->setup($adapter);
    }

    /**
     * Инициализация адаптера.
     *
     * @param FilesystemAdapterInterface $adapter
     */
    private function setup(FilesystemAdapterInterface $adapter) : void
    {
        $this->filesystem = new Filesystem(
            $adapter->getAdapter(),
            ['visibility' => 'public']
        );
    }
}
