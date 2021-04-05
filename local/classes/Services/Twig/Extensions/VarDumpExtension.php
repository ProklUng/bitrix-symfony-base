<?php

namespace Local\Services\Twig\Extensions;

use ErrorException;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class VarDumpExtension
 * Расширенный var_dump.
 * @package Local\Services\Twig\Extensions
 *
 * @since 17.02.2021
 *
 */
class VarDumpExtension extends AbstractExtension
{
    /**
     * Return extension name.
     *
     * @return string
     */
    public function getName() : string
    {
        return 'twig/var-dump-extension';
    }

    /**
     * Функции.
     *
     * @return TwigFunction[]
     */
    public function getFunctions() : array
    {
        return [
            new TwigFunction('dump_custom', [$this, 'dump']),
        ];
    }

    /**
     * dump_native().
     *
     * @param mixed $var Переменная.
     *
     * @return string
     *
     * @throws ErrorException
     *
     * @since 17.02.2021
     */
    public function dump($var) : string
    {
        $dumper = new HtmlDumper();
        $cloner = new VarCloner();

        $vars = func_get_args();
        $dump = fopen('php://memory', 'r+b');

        $dumper->setCharset('utf-8');

        foreach ($vars as $value) {
            $dumper->dump($cloner->cloneVar($value));
        }

        return stream_get_contents($dump, -1, 0);
    }
}
