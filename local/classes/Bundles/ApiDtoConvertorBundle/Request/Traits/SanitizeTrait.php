<?php

namespace Local\Bundles\ApiDtoConvertorBundle\Request\Traits;

use Local\Services\Sanitizing\SanitizableTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait SanitizeTrait
 * Валидация.
 * @package Local\Bundles\ApiDtoConvertorBundle\Request\Traits
 *
 * @since 04.11.2020
 */
trait SanitizeTrait
{
    /**
     * Санация Request.
     *
     * @param Request $request Request.
     * @param string  $dto     Class DTO.
     *
     * @return Request
     */
    private function sanitizeDto(Request $request, $dto): Request
    {
        $controller = $request->attributes->get('_controller')[0];
        $traits = class_uses_recursive($controller);

        // Подлежит санитизации.
        if (in_array(SanitizableTrait::class, $traits, true)
            &&
            method_exists($dto, 'getRulesSanitization')
        ) {
            return $this->sanitizeRequest(
                $request,
                $dto::getRulesSanitization()
            );
        }

        return $request;
    }
}
