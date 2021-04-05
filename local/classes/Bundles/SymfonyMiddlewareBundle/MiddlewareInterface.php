<?php
declare(strict_types=1);

namespace Local\Bundles\SymfonyMiddlewareBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface MiddlewareInterface
{
    public function handle(Request $request): ?Response;
}
