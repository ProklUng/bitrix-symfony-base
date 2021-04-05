<?php
/*
 * (c) Antonny Cyrille <rewieer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Local\Bundles\TaskSchedulerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TaskPass
 * @package Local\Bundles\TaskSchedulerBundle\DependencyInjection\Compiler
 *
 * Adds services tagged with "ts.task" to the scheduler
 */
class EventDispatcherPass implements CompilerPassInterface {
  public function process(ContainerBuilder $container) {
    $definition = $container->findDefinition('ts.event_dispatcher');
    $tasks = $container->findTaggedServiceIds('ts.event_subscriber');

    foreach($tasks as $id => $tags) {
      $definition->addMethodCall("addSubscriber", [new Reference($id)]);
    }
  }
}