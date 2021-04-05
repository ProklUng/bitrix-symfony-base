<?php
declare(strict_types=1);

namespace Local\Bundles\SymfonyMiddlewareBundle\Controller;

final class ControllerMetadata
{
    private $controllerFqcn;
    private $controllerAction;

    public function __construct(string $controllerFqcn, string $controllerAction)
    {
        $this->controllerFqcn = $controllerFqcn;
        $this->controllerAction = $controllerAction;
    }

    public function getControllerFqcn(): string
    {
        return $this->controllerFqcn;
    }

    public function getControllerAction(): string
    {
        return $this->controllerAction;
    }
}
