<?php

namespace Local\Bundles\TaskSchedulerBundle\Examples;

use Local\Bundles\TaskSchedulerBundle\Task\AbstractScheduledTask;
use Local\Bundles\TaskSchedulerBundle\Task\Schedule;

/**
 * Class ExampleTask
 * @package Local\Bundles\TaskSchedulerBundle\Examples
 *
 * @since 10.12.2020
 */
class ExampleTask extends AbstractScheduledTask
{
    protected function initialize(Schedule $schedule) {
        $schedule
            ->everyMinutes(60); // Perform the task every 60 minutes
    }

    public function run() {
        $path = container()->getParameter('kernel.project_dir');

        file_put_contents(
            $path . '/test.log',
            'Test'
        );
    }
}