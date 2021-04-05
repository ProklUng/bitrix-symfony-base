<?php
declare(strict_types=1);

namespace Local\Bundles\SymfonyMiddlewareBundle\Controller;

final class ControllerParser implements ControllerParserInterface
{
    public function parse(callable $controller): ControllerMetadata
    {
        if (is_array($controller)) {
            return new ControllerMetadata(get_class($controller[0]), $controller[1]);
        }

        return new ControllerMetadata(get_class($controller), '__invoke');
    }
}
