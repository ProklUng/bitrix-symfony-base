<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Local\ConsoleJedi\Application\Command;

/**
 * Base class for console command.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @return \Local\ConsoleJedi\Application\Application
     */
    public function getApplication()
    {
        return parent::getApplication();
    }
}