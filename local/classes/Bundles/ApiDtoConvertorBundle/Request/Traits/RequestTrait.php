<?php

namespace Local\Bundles\ApiDtoConvertorBundle\Request\Traits;

use Symfony\Component\HttpFoundation\Request;

/**
 * Trait RequestTrait
 * @package Local\Bundles\ApiDtoConvertorBundle\Request\Traits
 *
 * @since 05.11.2020
 */
trait RequestTrait
{
    /**
     * Данные запроса в зависимости от типа запроса.
     *
     * @param Request $request Request.
     *
     * @return array
     */
    private function getRequestData(Request $request) : array
    {
        // Тип запроса.
        $typeRequest = $request->getMethod();

        return $typeRequest !== 'GET' ?
            $request->request->all()
            :
            $request->query->all();
    }
}
