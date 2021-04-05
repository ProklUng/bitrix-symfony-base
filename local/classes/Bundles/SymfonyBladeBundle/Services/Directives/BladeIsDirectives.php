<?php

namespace Local\Bundles\SymfonyBladeBundle\Services\Directives;

use Illuminate\View\Compilers\BladeCompiler;
use Local\Bundles\SymfonyBladeBundle\Services\Directives\Utils\Parser;

/**
 * Class BladeIsDirectives
 * Logical directives.
 * @package Local\Bundles\SymfonyBladeBundle\Services\Directives
 *
 * @since 09.03.2021
 */
class BladeIsDirectives implements BladeDirectiveInterface
{
    /**
     * @inheritDoc
     */
    public function handlers(BladeCompiler $compiler) : array
    {
        return [
            /*
            |---------------------------------------------------------------------
            | @istrue / @isfalse
            |---------------------------------------------------------------------
            |
            | These directives can be used in different ways.
            | @istrue($v) Echo this @endistrue, @istrue($v, 'Echo this')
            | or @istrue($variable, $echoThisVariables)
            |
            */
            'istrue' => function (string $expression) : string {
                if (strpos($expression, ',') !== false) {
                    $collection = Parser::multipleArgs($expression);

                    return implode('', [
                        "<?php if (isset({$collection->get(0)}) && (bool) {$collection->get(0)} === true) : ?>",
                        "<?php echo {$collection->get(1)}; ?>",
                        '<?php endif; ?>',
                    ]);
                }

                return "<?php if (isset({$expression}) && (bool) {$expression} === true) : ?>";
            },

            'endistrue' => function (string $expression) : string {
                return '<?php endif; ?>';
            },

            'isfalse' => function (string $expression) : string {
                if (strpos($expression, ',') !== false) {
                    $collection = Parser::multipleArgs($expression);

                    return implode('', [
                        "<?php if (isset({$collection->get(0)}) && (bool) {$collection->get(0)} === false) : ?>",
                        "<?php echo {$collection->get(1)}; ?>",
                        '<?php endif; ?>',
                    ]);
                }

                return "<?php if (isset({$expression}) && (bool) {$expression} === false) : ?>";
            },

            'endisfalse' => function (string $expression) : string {
                return '<?php endif; ?>';
            },

            /*
            |---------------------------------------------------------------------
            | @isnull / @isnotnull
            |---------------------------------------------------------------------
            |
            | These directives can be used in different ways.
            | @isnull($v) Echo this @endisnull, @isnull($v, 'Echo this')
            | or @isnull($variable, $echoThisVariables)
            |
            */

            'isnull' => function (string $expression) : string {
                if (strpos($expression, ',') !== false) {
                    $collection = Parser::multipleArgs($expression);

                    return implode('', [
                        "<?php if (is_null({$collection->get(0)})) : ?>",
                        "<?php echo {$collection->get(1)}; ?>",
                        '<?php endif; ?>',
                    ]);
                }

                return "<?php if (is_null({$expression})) : ?>";
            },

            'endisnull' => function (string $expression) : string {
                return '<?php endif; ?>';
            },

            'isnotnull' => function (string $expression) : string {
                if (strpos($expression, ',') !== false) {
                    $collection = Parser::multipleArgs($expression);

                    return implode('', [
                        "<?php if (! is_null({$collection->get(0)})) : ?>",
                        "<?php echo {$collection->get(1)}; ?>",
                        '<?php endif; ?>',
                    ]);
                }

                return "<?php if (! is_null({$expression})) : ?>";
            },

            'endisnotnull' => function (string $expression) : string {
                return '<?php endif; ?>';
            },
        ];
    }
}
