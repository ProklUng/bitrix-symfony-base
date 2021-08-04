<?php

namespace Local\ComposerScript;

use Composer\Script\Event;

/**
 * Class RunMigrationsHandler
 * @package Local\ComposerScript
 */
class RunMigrationsHandler
{
    /**
     * @param Event $event
     *
     * @return void
     */
    public static function doInstall(Event $event): void
    {
        $io = $event->getIO();

        $output = shell_exec('php migrator migrate');
        $io->write($output);
    }
}