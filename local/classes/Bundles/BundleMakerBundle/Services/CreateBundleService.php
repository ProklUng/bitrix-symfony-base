<?php

namespace Local\Bundles\BundleMakerBundle\Services;

/**
 * Class CreateBundleService
 * @package Local\Bundles\BundleMakerBundle\Services
 */
class CreateBundleService
{
    /**
     * @const string PATH_BUNDLES_CONFIG Путь к файлу с конфигурациями бандлов.
     */
    private const PATH_BUNDLES_CONFIG_FILE = '/local/configs/standalone_bundles.php';

    /**
     * @const string PATH_BUNDLES_CONFIG_DIR Путь к директории с конфигурациями бандлов.
     */
    private const PATH_BUNDLES_CONFIG_DIR = '/local/configs';

    /**
     * @const string NAMESPACE_BUNDLES Пространство имен бандлов.
     */
    private const NAMESPACE_BUNDLES = 'Local\\Bundles\\';

    public const RESSOURCE_DIR = '/Resources/config';
    public const DEPENDENCY_DIR = '/DependencyInjection';

    /** @var string[]  */
    private $otherDirs = ['/Controller', '/Services', '/Tests'];

    /** @var string */
    private $ressourcesDir = '/Resources/config';

    /** @var string */
    private $dependencyDir = '/DependencyInjection';

    /** @var int */
    private $dirMode;

    /** @var string */
    private $workingDir;

    /** @var string */
    private $errMsg;

    /** @var string */
    private $bundleName;

    /** @var array */
    private $templateFiles;

    /**
     * @see https://php.net/mkdir for file modes
     *
     * @param string  $bundleName    Name of the new bundle (PascalCase).
     * @param string  $workingDir    The directory the bundle resides in.
     * @param integer $dirMode       The file mode of the to be created directories.
     * @param array   $templateFiles Array of template file paths.
     */
    public function __construct(
        string $bundleName,
        string $workingDir,
        int $dirMode = 0755,
        array $templateFiles = []
    ) {
        $this->bundleName = $bundleName;
        $this->workingDir = $workingDir;
        $this->dirMode = $dirMode;
        $this->templateFiles = $templateFiles;
    }

    /**
     * Создать директорию бандла.
     *
     * @return boolean
     */
    public function createBundleDirectories(): bool
    {
        if (true !== $this->createDir($this->workingDir)) {
            return false;
        }
        $resDir = $this->workingDir . self::RESSOURCE_DIR;
        if (true !== $this->createDir($resDir)) {
            return false;
        }
        $this->ressourcesDir = $resDir;
        $depDir = $this->workingDir . self::DEPENDENCY_DIR;
        if (true !== $this->createDir($depDir)) {
            return false;
        }
        $this->dependencyDir = $depDir;
        foreach ($this->otherDirs as $dir) {
            $oDir = $this->workingDir . $dir;
            if (true !== $this->createDir($oDir)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return boolean
     */
    public function copyRessourceFiles(): bool
    {
        foreach ($this->templateFiles as $key => $template) {
            if ($key === 'services' || $key === 'routes') {
                $dest = $this->ressourcesDir .'/'.$key.'.yaml';
                if (true !== copy($template, $dest)) {
                    $this->errMsg = 'Cannot copy ' . $key . '.yaml to  ' . $this->ressourcesDir;
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Creating Bundle.php and Extension.php.
     *
     * @return void
     */
    public function createBundleClasses(): void
    {
        $datum = date('d.m.Y');
        $shortName = str_replace('Bundle', '', $this->bundleName);
        $bundleSmallShortName = strtolower($shortName);
        foreach ($this->templateFiles as $key => $template) {
            if ($key === 'bundle' || $key === 'extension') {
                $content = file_get_contents($template);
                $content = preg_replace('/\{#bundleName\}/', $this->bundleName, $content);
                $content = preg_replace('/\{#bundleShortName\}/', $shortName, $content);
                $content = preg_replace('/\{#bundleSmallShortName\}/', $bundleSmallShortName, $content);
                $content = preg_replace('/\{#datum\}/', $datum, $content);
                $fileName = ($key === 'bundle') ? $this->bundleName . '.php' : $shortName . 'Extension.php';
                $dest = ($key === 'bundle') ? $this->workingDir . "/$fileName" : $this->dependencyDir . "/$fileName";

                file_put_contents($dest, $content);
            }
        }
    }

    /**
     * Запись в bundles.php
     *
     * @return boolean
     */
    public function activateBundle(): bool
    {
        $className = self::NAMESPACE_BUNDLES . $this->bundleName . '\\' . $this->bundleName;
        $bundlePhp = getcwd() . self::PATH_BUNDLES_CONFIG_FILE;
        $backUp = getcwd() . self::PATH_BUNDLES_CONFIG_FILE . '.backup';

        /** Директория, в которой лежат конфигурации бандлов. */
        $configDir = getcwd() . self::PATH_BUNDLES_CONFIG_DIR;

        if (!is_dir($configDir)) {
            $this->errMsg = 'Cannot find directory ' . $configDir;
            return false;
        }

        if (!is_writable($configDir)) {
            $this->errMsg = 'Cannot write in directory ' . $configDir;
            return false;
        }

        if (!is_file($bundlePhp)) {
            $this->errMsg = 'Cannot find file ' . $bundlePhp;
            return false;
        }

        if (!is_readable($bundlePhp)) {
            $this->errMsg = 'Cannot read file ' . $bundlePhp;
            return false;
        }

        if (!is_writable($bundlePhp)) {
            $this->errMsg = 'Cannot write in file ' . $bundlePhp;
            return false;
        }

        $contentArray = file($bundlePhp);
        if (!rename($bundlePhp, $backUp)) {
            $this->errMsg = 'Cannot create backup ' . $backUp;
            return false;
        }

        if (!touch($bundlePhp)) {
            $this->errMsg = 'Cannot create new file ' . $bundlePhp;
            return false;
        }

        $fp = fopen($bundlePhp, 'wb');
        foreach ($contentArray as $line) {
            if (preg_match('/(\];)/', $line)) {
                $newLine = "\t$className::class => ['all' => true],\n";
                fwrite($fp, $newLine);
            }
            fwrite($fp, $line);
        }

        fclose($fp);

        return true;
    }

    /**
     * Ошибки.
     *
     * @return string
     */
    public function getErrMsg(): string
    {
        return $this->errMsg;
    }

    /**
     * Creating directories.
     *
     * @param string $dir Директория.
     *
     * @return boolean
     */
    private function createDir(string $dir): bool
    {
        if (is_dir($dir)) {
            $this->errMsg = 'Cannot create existing directory ' . $dir;
            return false;
        }
        if (!mkdir($dir, $this->dirMode, true) && !is_dir($dir)) {
            $this->errMsg = 'Cannot create directory ' . $dir;
            return false;
        }

        return true;
    }
}
