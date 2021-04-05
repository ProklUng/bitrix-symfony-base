<?php

namespace Local\Bundles\BitrixWebformBundle\Services\Validators;

use Bitrix\Main\Application;
use Local\Bundles\BitrixWebformBundle\Services\FormSearcher;

/**
 * Class CFormValidatorUnique
 * @package Local\Bundles\BitrixWebformBundle\Services\Validators
 *
 * @since 06.02.2021
 */
class CFormValidatorUnique extends AbstractCustomBitrixWebformValidator
{
    /**
     * @var FormSearcher $formSearcher
     */
    private $formSearcher;

    /**
     * @var string $errorMessage
     */
    private $errorMessage = '#FIELD_NAME#: такое значение уже существует в базе.';

    /**
     * CFormValidatorUnique constructor.
     *
     * @param FormSearcher $formSearcher
     */
    public function __construct(FormSearcher $formSearcher)
    {
        $this->formSearcher = $formSearcher;
    }

    /**
     * @inheritDoc
     */
    public function GetDescription() : array
    {
        return [
            'NAME' => 'unique_value', // validator string ID
            'DESCRIPTION' => 'Проверка поля на уникальность', // validator description
            'TYPES' => ['text', 'textarea'], //  list of types validator can be applied.
            'HANDLER' => [$this, 'DoValidate'] // main validation method
        ];
    }

    /**
     * @inheritDoc
     */
    public function DoValidate($arParams, $arQuestion, $arAnswers, $arValues) : bool
    {
        global $APPLICATION;

        // Попытка избежать трудностей с обновлением формы в админке.
        $request = Application::getInstance()->getContext()->getRequest();
        if ($request->isAdminSection()) {
            return true;
        }

        $this->formSearcher->setIdForm((int)$arQuestion['FORM_ID']);

        foreach ($arValues as $value) {
            $this->formSearcher->addFilter(
                [$arQuestion['SID'] => $value]
            );

            if ($value && $this->formSearcher->exist()) {
                $APPLICATION->ThrowException($this->errorMessage);
                return false;
            }
        }

        return true;
    }
}
