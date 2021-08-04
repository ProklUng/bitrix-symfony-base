<?php

namespace Local\ComposerScript;

use Composer\Script\Event;

/**
 * Class CacheWarmerHandler
 * @package Local\ComposerScript
 *
 * @since 04.08.2021
 */
class CacheWarmerHandler
{
    /**
     * @param Event $event
     *
     * @return void
     */
    public static function doInstall(Event $event): void
    {
        $io = $event->getIO();

        $output = shell_exec('php bin/console cache:clear');
        $io->write($output);

        $output = shell_exec('php bin/console cache:warm');
        $io->write($output);
    }
}