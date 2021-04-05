<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Local\ConsoleJedi\Module\Command;

use Local\ConsoleJedi\Application\CanRestartTrait;
use Local\ConsoleJedi\Module\Exception\ModuleInstallException;
use Local\ConsoleJedi\Module\Module;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for module installation / register.
 *
 * @author Marat Shamshutdinov <m.shamshutdinov@gmail.com>
 */
class LoadCommand extends ModuleCommand
{
    use CanRestartTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('module:load')
            ->setDescription('Load and install module from Marketplace')
            ->addOption('no-update', 'nu', InputOption::VALUE_NONE, 'Don\' update module')
            ->addOption('no-register', 'ni', InputOption::VALUE_NONE, 'Load only, don\' register module')
            ->addOption('beta', 'b', InputOption::VALUE_NONE, 'Allow the installation of beta releases');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = new Module($input->getArgument('module'));

        if (!$module->isThirdParty()) {
            $output->writeln('<info>Loading kernel modules is unsupported</info>');
        }

        if ($input->getOption('beta')) {
            $module->setBeta();
        }

        $module->load();

        if (!$input->getOption('no-update')) {
            $modulesUpdated = null;
            while ($module->update($modulesUpdated)) {
                if (is_array($modulesUpdated)) {
                    foreach ($modulesUpdated as $moduleName => $moduleVersion) {
                        $output->writeln(sprintf('updated %s to <info>%s</info>', $moduleName, $moduleVersion));
                    }
                }
                return $this->restartScript($input, $output);
            }
        }

        if (!$input->getOption('no-register')) {
            try {
                $module->register();
            } catch (ModuleInstallException $e) {
                $output->writeln(sprintf('<comment>%s</comment>', $e->getMessage()),
                    OutputInterface::VERBOSITY_VERBOSE);
                $output->writeln(sprintf('Module loaded, but <error>not registered</error>. You need to do it yourself in admin panel.',
                    $module->getName()));
            }
        }

        $output->writeln(sprintf('installed <info>%s</info>', $module->getName()));

        return 0;
    }
}