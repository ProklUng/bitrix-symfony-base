<?php

namespace Local\Bundles\ApiDtoConvertorBundle\Request\Traits;

use Local\Services\Validation\Controllers\ValidateableTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait ValidateTrait
 * Валидация.
 * @package Local\Bundles\ApiDtoConvertorBundle\Request\Traits
 *
 * @since 04.11.2020
 */
trait ValidateTrait
{
    /**
     * Валидация.
     *
     * @param Request $request Request.
     * @param object  $dto     DTO.
     */
    private function validateRequest(Request $request, $dto): void
    {
        $controller = $request->attributes->get('_controller')[0];
        $traits = class_uses_recursive($controller);

        // Подлежит валидации.
        if (in_array(ValidateableTrait::class, $traits, true)
            &&
            method_exists($dto, 'getRules')
        ) {
            $this->validate(
                $dto->toArray(),
                $dto->getRules()
            );
        }
    }
}
