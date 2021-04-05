<?php
declare(strict_types=1);

namespace Local\Bundles\SymfonyMiddlewareBundle\Controller;

interface ControllerParserInterface
{
    public function parse(callable $controller): ControllerMetadata;
}
