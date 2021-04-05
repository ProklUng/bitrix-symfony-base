<?php

namespace Local\Services\Sanitizing;

use Symfony\Component\HttpFoundation\Request;
use Waavi\Sanitizer\Sanitizer;

/**
 * Trait SanitizableTrait
 * @package Local\Services\Sanitizing
 *
 * @since 07.09.2020
 * @since 08.09.2020 Доработка.
 */
trait SanitizableTrait
{
    /**
     * @var SanitizerInterface $sanitizer Санитайзер.
     */
    protected $sanitizer;

    /**
     * @required
     *
     * Задать санитайзер.
     *
     * @param SanitizerInterface $sanitizer Санитайзер.
     *
     * @return $this
     *
     * @since 08.09.2020
     */
    public function setSanitizer(SanitizerInterface $sanitizer) : self
    {
        $this->sanitizer = $sanitizer;

        return $this;
    }

    /**
     * Санитизирует переданные данные.
     *
     * @param array      $data  Данные.
     * @param array|null $rules Правила валидации.
     *
     * @return array
     */
    public function sanitize(array $data, array $rules = null): array
    {
        $rules = $rules ?? (property_exists($this, 'sanitizeRules') ? $this->sanitizeRules : []);

        $sanitizer = new Sanitizer($data, $rules);

        return $sanitizer->sanitize();
    }

    /**
     * Санитизирует Request.
     *
     * @param Request    $request Request.
     * @param array|null $rules   Правила валидации.
     *
     * @return Request
     */
    public function sanitizeRequest(
        Request $request,
        array $rules = null
    ) : Request {
        $resultRequest = $request;

        // Тип запроса.
        $typeRequest = $request->getMethod();

        $data = ($typeRequest === 'POST') ?
            $request->request->all()
            :
            $request->query->all();

        if (empty($data)) {
            return $resultRequest;
        }

        // В свойстве sanitizeRules может лежать схема валидации.
        $rules = $rules ?? (property_exists($this, 'sanitizeRules') ? $this->sanitizeRules : []);

        $sanitizer = $this->sanitizer ? $this->sanitizer->make($data, $rules) : new Sanitizer($data, $rules);
        $arResult = $sanitizer->sanitize();

        // Обновить значения.
        if ($typeRequest === 'POST') {
            $resultRequest->request->add($arResult);
        }

        if ($typeRequest === 'GET') {
            $resultRequest->query->add($arResult);
        }

        return $resultRequest;
    }
}
