<?php

namespace Local\Commands\Runner;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Class CommandRunner
 * @package Local\Commands
 *
 * @since 02.04.2021
 */
class CommandRunner
{
    /** @var integer */
    protected $limit = 5;

    /** @var ArrayCollection|Process[] */
    protected $openProcesses = [];

    /** @var bool */
    protected $active = false;

    /** @var ArrayCollection|Process[] */
    protected $activeProcesses;

    /** @var ArrayCollection|Process[] */
    protected $completedProcesses;

    /** @var SymfonyStyle */
    protected $io;

    /** @var ProgressBar */
    protected $progressBar;

    /** @var string */
    protected $binary;

    /** @var string */
    protected $subPath;

    /** @var ArrayCollection */
    protected $errors;

    /** @var boolean */
    protected $continueOnError = true;

    /**
     * CommandRunner constructor.
     *
     * @param array       $processes
     * @param null|string $binary
     */
    public function __construct(array $processes, $binary = null)
    {
        $this->openProcesses = new ArrayCollection($processes);
        $this->activeProcesses = new ArrayCollection();
        $this->completedProcesses = new ArrayCollection();
        $this->errors = new ArrayCollection();

        $finder = new PhpExecutableFinder();
        $this->subPath = $_SERVER['PHP_SELF'] ?? $_SERVER['SCRIPT_NAME'] ?? $_SERVER['SCRIPT_FILENAME'];
        if ($binary === null) {
            $this->setPhpBinary($finder->find());
        } else {
            $this->setBinary($binary);
        }
    }

    /**
     * @param boolean $continue
     *
     * @return $this
     */
    public function continueOnError($continue = true): self
    {
        $this->continueOnError = $continue;

        return $this;
    }

    /**
     * The lock handler only works if you're using just one server.
     * If you have several hosts, you must not use this.
     *
     * @param string $command
     * @param string $lockName
     *
     * @return LockInterface
     */
    public static function lock(string $command, $lockName = ''): LockInterface
    {
        # TODO: dont use flockstore if user doesnt want to use it.
        $store = new FlockStore();
        $factory = new LockFactory($store);
        $lock = $factory->createLock($command.$lockName, 0, true);
        if (!$lock->acquire()) {
            exit(1);
        }

        return $lock;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->start();
        while ($this->hasOpenProcesses()) {
            if (!$this->process()) {
                break;
            }
            usleep(5000);
        }
        $this->finish();
    }

    /**
     * @return ArrayCollection
     */
    public function getErrors(): ArrayCollection
    {
        return $this->errors;
    }

    /**
     * @return boolean
     */
    public function hasOpenProcesses(): bool
    {
        return !$this->openProcesses->isEmpty() || !$this->activeProcesses->isEmpty();
    }

    /**
     * @return void
     */
    private function start(): void
    {
        $this->active = true;
        if ($this->io) {
            $this->createProgressBar();
        }
    }

    /**
     * @param string $subPath
     *
     * @return $this
     */
    public function setSubPath(string $subPath): CommandRunner
    {
        $this->subPath = $subPath;

        return $this;
    }

    /**
     * @param SymfonyStyle $io
     *
     * @return $this
     */
    public function setIO(SymfonyStyle $io): CommandRunner
    {
        $this->io = $io;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param integer $limit
     *
     * @return $this
     */
    public function setLimit($limit = 5): CommandRunner
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param string $binary
     *
     * @return $this
     */
    public function setPhpBinary($binary): CommandRunner
    {
        if (!$binary) {
            $this->io->error('Unable to find PHP binary.');
            exit(500);
        }

        $this->setBinary($binary);

        return $this;
    }

    /**
     * @param string $binary
     *
     * @return $this
     */
    public function setBinary($binary): CommandRunner
    {
        $this->binary = $binary;

        return $this;
    }

    /**
     * Create styled progressbar.
     *
     * @return void
     */
    private function createProgressBar(): void
    {
        $progressBar = $this->io->createProgressBar(count($this->openProcesses) * 2);
        $progressBar->setFormat("%current%/%max% [%bar%] %percent:3s%% | %elapsed% \n%message%\n");
        $progressBar->setBarCharacter('<fg=green>▓</>');
        $progressBar->setEmptyBarCharacter('<fg=red>░</>');
        $this->progressBar = $progressBar;
        $this->progressBar->start();
    }

    /**
     * @return boolean
     */
    private function process(): bool
    {
        if ($this->activeProcesses->count() < $this->limit) {
            $this->spawnNextProcess();
        }

        return $this->validateRunningProcesses();
    }

    /**
     * Spawns next process
     *
     * @return void
     */
    private function spawnNextProcess(): void
    {
        if (!$this->openProcesses->isEmpty()) {
            /** @var Process $process */
            $orgiginProcess = $this->openProcesses->first();
            $process = $this->modifyCommand($orgiginProcess);
            $this->activeProcesses->add($process);

            if ($this->progressBar) {
                $this->progressBar->setMessage($process->getCommandLine());
                $this->progressBar->display();
            }
            $process->start();
            $removed = $this->openProcesses->removeElement($orgiginProcess);

            if ($this->progressBar) {
                $this->progressBar->setProgress($this->progressBar->getProgress() + 1);
            }
        }
    }

    /**
     * @param Process $process
     *
     * @return Process
     */
    private function modifyCommand(Process $process): Process
    {
        return Process::fromShellCommandline(sprintf(
            '%s %s %s',
            $this->binary,
            $this->subPath,
            $process->getCommandLine()
        ));
    }

    /**
     * @return boolean
     */
    private function validateRunningProcesses(): bool
    {
        $activeProcesses = $this->activeProcesses;

        foreach ($activeProcesses as $key => $activeProcess) {
            if (!$activeProcess->isRunning()) {
                if ($activeProcess->getErrorOutput()) {
                    $this->errors->add([
                        'command' => $activeProcess->getCommandLine(),
                        'error' => $activeProcess->getErrorOutput(),
                    ]);
                    if (!$this->continueOnError) {
                        return false;
                    }
                }

                $this->completedProcesses->add($activeProcess);
                $this->activeProcesses->remove($key);
                if ($this->progressBar) {
                    $this->progressBar->setProgress($this->progressBar->getProgress() + 1);
                }
            }
            usleep(5000);
        }

        return true;
    }

    /**
     * @return void
     */
    private function finish() : void
    {
        if (!$this->errors->isEmpty()) {
            foreach ($this->errors as $error) {
                $this->io->warning($error);
            }
        }

        $this->active = false;
    }
}