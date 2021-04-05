<?php

namespace Local\Commands;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Data\ManagedCache;
use Bitrix\Main\Data\StaticHtmlCache;
use CBitrixComponent;
use CFileCacheCleaner;
use CHTMLPagesCache;
use CStackCacheManager;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CacheClear
 * @package Local\Commands
 *
 * @since 10.12.2020
 * @internal Форк из пакета https://github.com/Vampiref92/BitrixBase. Выпилил логгер, упростил и т.д.
 */
class CacheClear extends Command
{
    public const ARG_CACHE_TYPE = 'cache-type';

    public const OPT_CACHE_PATH = 'cache-path';

    /**
     * Конфигурация.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('cache:clear')
            ->setDescription('Clear cache')
            ->addArgument(
                self::ARG_CACHE_TYPE,
                InputArgument::OPTIONAL,
                'Cache type [all, menu, managed, html]',
                'all'
            )
            ->addOption(
                self::OPT_CACHE_PATH,
                'path',
                InputOption::VALUE_OPTIONAL,
                'Cache path',
                ''
            );
    }

    /**
     * Исполнение команды.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheType = $input->getArgument(self::ARG_CACHE_TYPE);
        $cachePath = $input->getOption(self::OPT_CACHE_PATH);
        $cacheEngine = Cache::getCacheEngineType();

        echo 'Clear cache start';
        echo 'Cache Engine: '.$cacheEngine;
        echo 'Cache Type: '.$cacheType;

        if ($cachePath) {
            echo 'Cache Path: '.$cachePath;
        }

        $this->fileCacheClean($cacheType, $cacheEngine, $cachePath);
        Application::getInstance()->getManagedCache()->cleanAll();

        if (!$cachePath) {
            switch ($cacheType) {
                case 'menu':
                    (new ManagedCache())->CleanDir('menu');

                    CBitrixComponent::clearComponentCache('bitrix:menu');
                    break;
                case 'managed':
                    if (class_exists('\Bitrix\Main\Data\ManagedCache')) {
                        (new ManagedCache())->cleanAll();
                    }

                    /**
                     * Приходится тупо грохать. Битрикс переименовывает папк MY_SQL и ставит агента,
                     * на удаление "потом". В консольном случе это "потом не наступает".
                     */
                    $this->rrmdir($_SERVER['DOCUMENT_ROOT'].'/bitrix/managed_cache');

                    if (class_exists('\CStackCacheManager')) {
                        (new CStackCacheManager())->CleanAll();
                    }

                    break;
                case 'html':
                    if (class_exists('\Bitrix\Main\Data\StaticHtmlCache')) {
                        StaticHtmlCache::getInstance()->deleteAll();
                    }
                    break;
                case 'all':
                    BXClearCache(true);

                    if (class_exists('\Bitrix\Main\Data\ManagedCache')) {
                        (new ManagedCache())->cleanAll();
                    }

                    $this->rrmdir($_SERVER['DOCUMENT_ROOT'].'/bitrix/managed_cache');

                    if (class_exists('\Bitrix\Main\Data\StaticHtmlCache')) {
                        StaticHtmlCache::getInstance()->deleteAll();
                    }

                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Рекурсивно удалить папку со всем файлами и папками.
     *
     * @param string $dir Директория.
     *
     * @return void
     */
    private function rrmdir(string $dir) : void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== "." && $object !== "..") {
                    if (filetype($dir. '/' .$object) === "dir") {
                        $this->rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir. '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * @param string $cacheType
     * @param string $cacheEngine
     * @param string $cachePath
     *
     * @return void
     */
    private function fileCacheClean(string $cacheType, string $cacheEngine, string $cachePath) : void
    {
        if ($cacheType === 'html' || $cacheEngine === 'cache_engine_files') {
            require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/cache_files_cleaner.php';
            $obCacheCleaner = new CFileCacheCleaner($cacheType);
            if (!$obCacheCleaner->InitPath($cachePath)) {
                throw new RuntimeException('Cant init File Cache Cleaner');
            }
        } else {
            return;
        }

        if (!$cachePath) {
            $_SESSION['CACHE_STAT'] = [];
        }

        $currentTime = time();

        $bDoNotCheckExpiredDate = in_array($cacheType, ['all', 'menu', 'managed', 'html'], true);

        if ($cacheType === 'html') {
            $obCacheCleaner->Start();
            $space_freed = 0;
            while ($file = $obCacheCleaner->GetNextFile()) {
                if (is_string($file) && !preg_match("/(\\.enabled|.config\\.php)\$/", $file)) {
                    $file_size = filesize($file);
                    $_SESSION['CACHE_STAT']['scanned']++;
                    $_SESSION['CACHE_STAT']['space_total'] += $file_size;
                    if (@unlink($file)) {
                        $_SESSION['CACHE_STAT']['deleted']++;
                        $_SESSION['CACHE_STAT']['space_freed'] += $file_size;
                        $space_freed += $file_size;
                    } else {
                        $_SESSION['CACHE_STAT']['errors']++;
                    }
                }
                usleep(2500);
            }
            CHTMLPagesCache::writeStatistic(false, false, false, false, -$space_freed);
        } elseif ($cacheEngine === 'cache_engine_files') {
            $obCacheCleaner->Start();
            while ($file = $obCacheCleaner->GetNextFile()) {
                if (is_string($file)) {
                    $date_expire = $obCacheCleaner->GetFileExpiration($file);
                    if ($date_expire) {
                        $file_size = filesize($file);
                        $_SESSION['CACHE_STAT']['scanned']++;
                        $_SESSION['CACHE_STAT']['space_total'] += $file_size;
                        if ($bDoNotCheckExpiredDate || ($date_expire < $currentTime)) {
                            if (@unlink($file)) {
                                $_SESSION['CACHE_STAT']['deleted']++;
                                $_SESSION['CACHE_STAT']['space_freed'] += $file_size;
                            } else {
                                $_SESSION['CACHE_STAT']['errors']++;
                            }
                        }
                    }
                }
                usleep(2500);
            }
        } else {
            $_SESSION['CACHE_STAT'] = [];
        }
    }
}
