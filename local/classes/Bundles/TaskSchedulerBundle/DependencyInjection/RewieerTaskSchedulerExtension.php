<?php
/*
 * (c) Antonny Cyrille <rewieer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Local\Bundles\TaskSchedulerBundle\DependencyInjection;

use Local\Bundles\TaskSchedulerBundle\Task\TaskInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class RewieerTaskSchedulerExtension extends Extension {
  public function load(array $configs, ContainerBuilder $container)
  {
    $container->registerForAutoconfiguration(TaskInterface::class)->addTag('ts.task');

    $configuration = new Configuration();
    $this->processConfiguration($configuration, $configs);

    $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
    $loader->load('services.xml');
  }
}
