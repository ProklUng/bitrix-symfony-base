<?php

namespace Local\Bundles\SymfonyBladeBundle\Services;

use Illuminate\View\Compilers\BladeCompiler;
use Local\Bundles\SymfonyBladeBundle\Services\Directives\BladeDirectiveInterface;

/**
 * Class DirectiveRegistrator
 * @package Local\Bundles\SymfonyBladeBundle\Services
 *
 * @since 09.03.2021
 */
class DirectiveRegistrator
{
    /**
     * @var BladeCompiler $compiler Blade Compiler.
     */
    private $compiler;

    /**
     * @var array $directives Директивы.
     */
    private $directives = [];

    /**
     * DirectiveRegistrator constructor.
     *
     * @param BladeCompiler $compiler      Blade Compiler.
     * @param mixed         ...$directives Сервисы, помеченные тэгом bitrix.custom.directive.
     */
    public function __construct(BladeCompiler $compiler, ... $directives)
    {
        $this->compiler = $compiler;

        $result = [];
        foreach ($directives as $directive) {
            $iterator = $directive->getIterator();
            $result[] = iterator_to_array($iterator);
        }

        $this->directives = array_merge($this->directives, ...$result);

        $this->register();
    }

    /**
     * Инициализация директив.
     *
     * @return void
     */
    public function register() : void
    {
        foreach ($this->directives as $directive) {
            /** @var BladeDirectiveInterface $directive */
            $pack = $directive->handlers($this->compiler);

            foreach ($pack as $name => $item) {
                $this->compiler->directive($name, $item);
            }
        }
    }
}
