<?php

namespace Local\Bundles\CustomRequestResponserBundle\Services\Contracts;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface PageSpeedMiddlewareInterface
 * @package Local\Bundles\CustomRequestResponserBundle\Services\Contracts
 *
 * @since 21.02.2021
 */
interface PageSpeedMiddlewareInterface
{
    /**
     * Apply rules.
     *
     * @param string $buffer Текстовый контент.
     *
     * @return string
     */
    public function apply(string $buffer) : string;

    /**
     * Should Process?
     *
     * @param Request  $request  Request.
     * @param Response $response Response.
     *
     * @return boolean
     */
    public function shouldProcessPageSpeed(Request $request, Response $response) : bool;
}