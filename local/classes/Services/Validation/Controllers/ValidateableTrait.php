<?php

namespace Local\Services\Validation\Controllers;

use Local\Services\Validation\Exceptions\ValidateErrorException;
use Local\Services\Validation\Traits\Validatable;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ValidateableTrait
 * Трэйт валидации для контроллеров.
 * @package Local\Services\Validation\Controllers
 *
 * @since 08.09.2020
 * @since 10.09.2020 Изменен тип исключений.
 */
trait ValidateableTrait
{
    use Validatable;

    /**
     * Валидирует Request.
     *
     * @param Request    $request Request.
     * @param array|null $rules   Правила валидации.
     *
     * @return boolean
     * @throws ValidateErrorException Ошибки валидации.
     */
    public function validateRequest(
        Request $request,
        array $rules = null
    ) : bool {
        // Тип запроса.
        $typeRequest = $request->getMethod();

        $data = ($typeRequest === 'POST') ?
            $request->request->all()
            :
            $request->query->all();

        if (empty($data)) {
            throw new ValidateErrorException('Empty input params.');
        }

        return $this->validate(
            $data,
            $rules
        );
    }
}
