<?php

namespace Local\SymfonyTools\Router\Annotations;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Class SearchAnnotatedClasses
 * @package Local\SymfonyTools\Router\Annotations
 *
 * @since 09.10.2020
 */
class SearchAnnotatedClasses
{
    /** @var array $paths Пути, где искать классы. */
    private $paths;

    /** @var array $classes Результат. Классы. */
    private $classes = [];
    /** @var string $documentRoot DOCUMENT_ROOT */
    private $documentRoot;

    /**
     * SearchAnnotatedClasses constructor.
     *
     * @param string     $documentRoot DOCUMENT_ROOT.
     * @param array|null $paths        Пути, где искать классы.
     */
    public function __construct(
        string $documentRoot,
        array $paths = null
    ) {
        $this->paths = $paths;
        $this->documentRoot = $documentRoot;
    }

    /**
     * Собрать классы по всем путям.
     *
     * @return array
     */
    public function collect() : array
    {
        if ($this->paths === null) {
            return [];
        }

        foreach ($this->paths as $path) {
            $this->classes = array_merge(
                $this->listClassesByPath($this->documentRoot . $path),
                $this->classes
            );
        }

        return $this->classes;
    }

    /**
     * Классы по пути.
     *
     * @param string $path Путь.
     *
     * @return array
     *
     * @internal Код с stackoverflow.
     */
    protected function listClassesByPath(string $path)
    {
        $fqcns = [];

        $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $phpFiles = new RegexIterator($allFiles, '/\.php$/');
        foreach ($phpFiles as $phpFile) {
            $content = file_get_contents($phpFile->getRealPath());
            $tokens = token_get_all($content);
            $namespace = '';
            for ($index = 0; isset($tokens[$index]); $index++) {
                if (!isset($tokens[$index][0])) {
                    continue;
                }
                if (T_NAMESPACE === $tokens[$index][0]) {
                    $index += 2; // Skip namespace keyword and whitespace
                    while (isset($tokens[$index]) && is_array($tokens[$index])) {
                        $namespace .= $tokens[$index++][1];
                    }
                }
                if (T_CLASS === $tokens[$index][0] && T_WHITESPACE === $tokens[$index + 1][0] && T_STRING === $tokens[$index + 2][0]) {
                    $index += 2; // Skip class keyword and whitespace
                    $fqcns[] = $namespace.'\\'.$tokens[$index][1];

                    # break if you have one class per file (psr-4 compliant)
                    # otherwise you'll need to handle class constants (Foo::class)
                    break;
                }
            }
        }

        return $fqcns;
    }
}
