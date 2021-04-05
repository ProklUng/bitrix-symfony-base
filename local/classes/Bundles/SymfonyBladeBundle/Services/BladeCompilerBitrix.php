<?php

namespace Local\Bundles\SymfonyBladeBundle\Services;

use Illuminate\View\Compilers\BladeCompiler as BaseCompiler;

/**
 * Class BladeCompilerBitrix
 * @package Local\Bundles\SymfonyBladeBundle\Services
 */
class BladeCompilerBitrix extends BaseCompiler
{
    /**
     * Compile the given Blade template contents.
     *
     * @param string $value Значение.
     *
     * @return string
     */
    public function compileString($value) : string
    {
        $result = '<?php if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true) die();?>';
        $result .= '<?php if(!empty($arResult)) extract($arResult, EXTR_SKIP);?>';

        return $result . parent::compileString($value);
    }
}
