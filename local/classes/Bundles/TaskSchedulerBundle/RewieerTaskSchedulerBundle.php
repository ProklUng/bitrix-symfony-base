<?php
/*
 * (c) Antonny Cyrille <rewieer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Local\Bundles\TaskSchedulerBundle;

use Local\Bundles\TaskSchedulerBundle\DependencyInjection\Compiler\EventDispatcherPass;
use Local\Bundles\TaskSchedulerBundle\DependencyInjection\Compiler\TaskPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RewieerTaskSchedulerBundle extends Bundle {
  public function build(ContainerBuilder $container) {
    $container->addCompilerPass(new TaskPass());
    $container->addCompilerPass(new EventDispatcherPass());
  }
}
