<?php

namespace Local\Bundles\TaskSchedulerBundle\Tests\Task;

use Local\Bundles\TaskSchedulerBundle\Task\TaskInterface;

class Task implements TaskInterface
{
    public static $runCount = 0;
    public $enable = true;

    public function isDue($currentTime): bool
    {
        return $this->enable;
    }

    public function getNextRunDates($counter): array
    {
        return [];
    }

    public function run()
    {
        static::$runCount++;
    }
}