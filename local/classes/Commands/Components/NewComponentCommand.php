<?php

namespace Local\Commands\Components;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class NewComponentCommand
 * Create new component.
 * @package Local\Commands\Components
 *
 * @since 14.12.2020
 */
class NewComponentCommand extends Command
{
    /** @var string BASE_DIR_COMPONENTS Директория, где лежат компоненты. */
    private const BASE_DIR_COMPONENTS = '/local/components/guta/';

    protected function configure()
    {
        $this
            ->setName('new:component')
            ->setAliases(['nc'])
            ->setDescription('create new bitrix component')
            ->addArgument('name', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $from = __DIR__ . '/templates/component';

        $to = $_SERVER['DOCUMENT_ROOT'] . self::BASE_DIR_COMPONENTS . $name;

        $this->copyFolder($from, $to);
        $this->compileTemplate($to . '/class.php', [
            '#CLASS_NAME#' => $this->strToCamelCase($name) . 'Component',
        ]);

        $output->writeln(sprintf(
            'Component created in path class %s.',
            self::BASE_DIR_COMPONENTS . $name
        ));

        return 1;
    }

    /**
     * Копировать папку.
     *
     * @param string  $fromDir Откуда.
     * @param string  $toDir   Куда.
     * @param boolean $update  Перезаписывать.
     * @param boolean $force
     *
     * @return void
     */
    private function copyFolder(string $fromDir, string $toDir, $update = true, $force = true)
    {
        if (is_dir($fromDir)) {
            $toDir = $this->mkdirSafe($toDir, $force);
            if (!$toDir) {
                return;
            }
            $d = dir($fromDir);
            while (false !== ($entry = $d->read())) {
                if ($entry != '.' && $entry != '..') {
                    $this->copyFolder("$fromDir/$entry", "$toDir/$entry", $update, $force);
                }

            }
            $d->close();
        } else {
            $this->copySafe($fromDir, $toDir, $update);
        }
    }

    /**
     * Создать директорию.
     *
     * @param string  $dir   Директория.
     * @param boolean $force
     *
     * @return mixed
     */
    private function mkdirSafe(string $dir, bool $force)
    {
        if (file_exists($dir)) {
            if (is_dir($dir)) {
                return $dir;
            } else if (!$force) {
                return false;
            }

            unlink($dir);
        }
        return (mkdir($dir, 0777, true)) ? $dir : false;
    }

    /**
     * @param string  $srcFolder  Исходная папка.
     * @param string  $destFolder Конечная папка.
     * @param boolean $update     Обновлять.
     *
     * @return boolean
     */
    private function copySafe(string $srcFolder, string $destFolder, bool $update)
    {
        $time1 = filemtime($srcFolder);
        if (file_exists($destFolder)) {
            $time2 = filemtime($destFolder);
            if ($time2 >= $time1 && $update) {
                return false;
            }

        }
        $ok = copy($srcFolder, $destFolder);
        if ($ok) {
            touch($destFolder, $time1);
        }

        return $ok;
    }

    /**
     * Скомпилировать файл class.php.
     *
     * @param string $filePath
     * @param array $dataUpdate
     *
     * @return void
     */
    private function compileTemplate(string $filePath, array $dataUpdate): void
    {
        $filePathTemplate = $filePath . '.template';

        $keys = array_keys($dataUpdate);
        $values = array_values($dataUpdate);
        $data = str_replace($keys, $values, file_get_contents($filePathTemplate));

        file_put_contents($filePath, $data);
        @unlink($filePathTemplate);
    }

    /**
     * @param string $str
     * @param array|string[] $delimiter
     *
     * @return string
     */
    private function strToCamelCase(string $str, array $delimiter = ['-', '_', '.', ' ']): string
    {
        foreach ($delimiter as $d) {
            $str = $this->toCamelCase($str, $d);
        }

        return $str;
    }

    /**
     * @param string $string
     * @param string $delimiter
     *
     * @return string
     */
    private function toCamelCase(string $string, string $delimiter = '-'): string
    {
        $result = '';
        $data = explode($delimiter, $string);
        foreach ($data as $part) {
            $result .= ucfirst($part);
        }

        return $result;
    }
}
