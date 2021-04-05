<?php

namespace Local\Bundles\CustomRequestResponserBundle\Services\Contracts;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface IndexRouteManagerInterface
 * @package Local\Bundles\CustomRequestResponserBundle\Services\Contracts
 *
 * @since 18.02.2021
 */
interface IndexRouteManagerInterface
{
    /**
     * Нужно индексировать? Параметр роута _no_index.
     *
     * @param Request $request Request.
     *
     * @return boolean
     */
    public function shouldIndex(Request $request) : bool;
}
