<?php

namespace Local\Bundles\SymfonyBladeBundle\Services\Directives;

use Illuminate\View\Compilers\BladeCompiler;

/**
 * Interface BladeDirectiveInterface
 * @package Local\Bundles\SymfonyBladeBundle\Services\Directives
 *
 * @since 09.03.2021
 */
interface BladeDirectiveInterface
{
    /**
     * Регистрация директив скопом.
     *
     * @param BladeCompiler $compiler Blade Compiler.
     *
     * @return array
     */
    public function handlers(BladeCompiler $compiler) : array;
}
