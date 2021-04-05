<?php
/**
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Local\ConsoleJedi\Application\Command;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Command application initialization.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class InitCommand extends Command
{
    const COMPLETED_LOGO = '
                      ____
                 _.\' :  `._
             .-.\'`.  ;   .\'`.-.
    __      / : ___\ ;  /___ ; \      __
  ,\'_ ""--.:__;".-.";: :".-.":__;.--"" _`,
  :\' `.t""--.. \'<@.`;_  \',@>` ..--""j.\' `;
       `:-.._J \'-.-\'L__ `-- \' L_..-;\'
         "-.__ ;  .-"  "-.  : __.-"
             L \' /.------.\ \' J
              "-.   "--"   .-"
             __.l"-:_JL_;-";.__
          .-j/\'.;  ;""""  / .\'\"-.
        .\' /:`. "-.:     .-" .\';  `.
     .-"  / ;  "-. "-..-" .-"  :    "-.
  .+"-.  : :      "-.__.-"      ;-._   \
  ; \  `.; ;                    : : "+. ;
  :  ;   ; ;                    : ;  : \:
 : `."-; ;  ;                  :  ;   ,/;
  ;    -: ;  :                ;  : .-"\'  :
  :\     \  : ;             : \.-"      :
   ;`.    \  ; :            ;.\'_..--  / ;
   :  "-.  "-:  ;          :/."      .\'  :
     \       .-`.\        /t-""  ":-+.   :
      `.  .-"    `l    __/ /`. :  ; ; \  ;
        \   .-" .-"-.-"  .\' .\'j \  /   ;/
         \ / .-"   /.     .\'.\' ;_:\'    ;
          :-""-.`./-.\'     /    `.___.\'
                \ `t  ._  /  bug :F_P:
                 "-.t-._:\'
                 
          Installation is completed.
          May the Force be with you.
     ';

    /**
     * @var string Path to directory with templates of the application files.
     */
    protected $tmplDir;
    /**
     * @var string Default name of directory with environments settings.
     */
    protected $envDir = 'environments';
    /**
     * @var QuestionHelper $question
     */
    protected $questionHelper;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Initialize the Console Jedi')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Override an existing files');
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->tmplDir = $_SERVER['DOCUMENT_ROOT'] . '/local/classes/ConsoleJedi/tmpl';
        $this->questionHelper = $this->getHelper('question');

        parent::initialize($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Install Console Jedi application</info>');

        $this->createEnvironmentsDir($input, $output);
        $this->createConfiguration($input, $output);

        $output->writeln('<info>' . static::COMPLETED_LOGO . '</info>');
    }

    /**
     * Creates directory with environments settings.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function createEnvironmentsDir(InputInterface $input, OutputInterface $output)
    {
        $targetDir = getcwd() . '/' . $this->envDir;
        $tmplDir = $this->tmplDir . '/environments';

        $output->writeln('  - Environment settings');

        if (file_exists($targetDir)) {
            $question = new ConfirmationQuestion(
                '    <error>Directory ' . $targetDir . ' already exists</error>' . PHP_EOL
                . '    <info>Overwrite? [Y/n]</info> ',
                true,
                '/^(y|j)/i'
            );

            if (!$this->questionHelper->ask($input, $output, $question)) {
                return;
            }
        }

        $fs = new Filesystem();
        $tmplIterator = new \RecursiveDirectoryIterator($tmplDir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($tmplIterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $item) {
            $itemPath = $targetDir . '/' . $iterator->getSubPathName();

            if ($item->isDir()) {
                $fs->mkdir($itemPath);
            } else {
                $fs->copy($item, $itemPath, true);
            }
        }

        $output->writeln('    Created directory settings of environments: <comment>' . $targetDir . '</comment>');
    }

    /**
     * Creates configuration file of application.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function createConfiguration(InputInterface $input, OutputInterface $output)
    {
        $path = $this->getApplication()->getRoot() . '/.jedi.php';

        $output->writeln('  - Configuration');

        if (file_exists($path)) {
            $question = new ConfirmationQuestion(
                '    <error>Configuration file ' . $path . ' already exists</error>' . PHP_EOL
                . '    <info>Overwrite? [Y/n]</info> ',
                true,
                '/^(y|j)/i'
            );

            if (!$this->questionHelper->ask($input, $output, $question)) {
                return;
            }
        }

        $fs = new Filesystem();

        $question = new Question('    <info>Enter path to web directory relative to '
            . $this->getApplication()->getRoot() . ':</info> ' . PHP_EOL
            . '    (or do not specify if you are already in the web directory)' . PHP_EOL);

        $question->setValidator(function ($answer) use ($fs) {
            $path = $answer;

            if ($answer === null) {
                $path = $this->getApplication()->getRoot();
            } elseif (!$fs->isAbsolutePath($answer)) {
                $path = $this->getApplication()->getRoot() . '/' . $answer;
            }

            if (!is_dir($path)) {
                throw new \RuntimeException('Directory "' . $path . '" is missing');
            }

            return $answer;
        });

        $webDir = $this->questionHelper->ask($input, $output, $question);

        $content = file_get_contents($this->tmplDir . '/.jedi.php');
        $content = str_replace(
            ['%web-dir%', '%env-dir%'],
            [addslashes($webDir), addslashes($this->envDir)],
            $content
        );
        $fs->dumpFile($path, $content);

        $output->writeln('    Created configuration file of application <comment>' . $path . '</comment>');
    }
}