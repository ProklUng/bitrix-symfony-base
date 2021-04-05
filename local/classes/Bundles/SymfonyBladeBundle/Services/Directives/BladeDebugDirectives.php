<?php

namespace Local\Bundles\SymfonyBladeBundle\Services\Directives;

use Illuminate\View\Compilers\BladeCompiler;

/**
 * Class BladeDebugDirectives
 * Debug directives.
 * @package Local\Bundles\SymfonyBladeBundle\Services\Directives
 *
 * @since 09.03.2021
 */
class BladeDebugDirectives implements BladeDirectiveInterface
{
    /**
     * @inheritDoc
     */
    public function handlers(BladeCompiler $compiler) : array
    {
        return [
            'dump' => function ($expression) {
                return "<?php dd({$expression});?>";
            },

            'dd' => function ($expression) {
                return "<?php dd({$expression});?>";
            },
        ];
    }
}
