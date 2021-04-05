<?php

namespace Local\Bundles\BitrixIblockElementValidatorBundle\Services;

use Bitrix\Main\Context;
use CIBlockProperty;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;
use Local\Bundles\BitrixIblockElementValidatorBundle\Services\Contracts\BitrixPropertyValidatorInterface;
use Local\Bundles\BitrixIblockElementValidatorBundle\Services\Contracts\SanitizerInterface;
use Local\Bundles\BitrixIblockElementValidatorBundle\Services\Exceptions\ValidateErrorException;
use RuntimeException;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class BitrixPropertiesValidator
 * @package Local\Bundles\BitrixIblockElementValidatorBundle\Services
 *
 * @since 07.02.2021
 */
class BitrixPropertiesValidator
{
    /**
     * @var array $config Конфигурация бандла.
     */
    private $config;

    /**
     * @var ServiceLocator $customValidators Кастомные валидаторы, помеченные тэгом
     *                                       bitrix_iblock_element_validator.custom_validator.
     */
    private $customValidators;

    /**
     * @var SanitizerInterface $sanitizer
     */
    private $sanitizer;

    /**
     * BitrixPropertiesValidator constructor.
     *
     * @param ServiceLocator     $serviceLocator Кастомные валидаторы, помеченные тэгом
     *                                           bitrix_iblock_element_validator.custom_validator.
     * @param SanitizerInterface $sanitizer      Санитайзер.
     * @param array              $config         Конфигурация бандла.
     */
    public function __construct(
        ServiceLocator $serviceLocator,
        SanitizerInterface $sanitizer,
        array $config
    ) {
        $this->config = $config;
        $this->customValidators = $serviceLocator;
        $this->sanitizer = $sanitizer;

        /** @psalm-suppress UndefinedFunction */
        AddEventHandler(
            'iblock',
            'OnBeforeIBlockElementUpdate',
            [$this, 'onBeforeIBlockElementUpdateHandler'],
            1
        );

        /** @psalm-suppress UndefinedFunction */
        AddEventHandler(
            'iblock',
            'OnBeforeIBlockElementAdd',
            [$this, 'onBeforeIBlockElementAddHandler'],
            1
        );
    }

    /**
     * Добавление элемента.
     *
     * @param array $arFields Поля элемента.
     *
     * @return mixed
     * @throws RuntimeException Внутренние ошибки.
     */
    public function onBeforeIBlockElementAddHandler(array &$arFields)
    {
        $errors = $errorsLocal = [];

        foreach ($this->config as $item) {
            if ($arFields['IBLOCK_ID'] !== (int)$item['id_iblock']) {
                continue;
            }

            $propertyId = $this->getPropertyIdByCode($item['id_iblock'], $item['code_property']);

            // Неверное свойство в конфиге. Или свойство есть, а его значений - нет.
            if ($propertyId === 0) {
                throw new RuntimeException(
                    sprintf(
                        'Не смог найти свойство %s в инфоблоке %s.',
                        $item['code_property'],
                        $item['id_iblock']
                    )
                );
            }

            if (!array_key_exists($arFields['PROPERTY_VALUES'], $item['code_property'])) {
                $arFields['PROPERTY_VALUES'][$item['code_property']] = '';
            }

            // Санация.
            if ($item['sanitize']) {
                $arFields['PROPERTY_VALUES'][$item['code_property']] = $this->sanitize(
                    $arFields['PROPERTY_VALUES'][$item['code_property']],
                    $item['sanitize']
                );
            }

            $valueProperty = (array)$arFields['PROPERTY_VALUES'][$item['code_property']];

            $resultValidation = $this->doValidate($valueProperty, $item);
            if (count($resultValidation) > 0) {
                $errorsLocal[] = $resultValidation;
            }
        }

        $errors = array_merge($errors, ...$errorsLocal);

        if (count($errors) > 0) {
            $GLOBALS['APPLICATION']->ThrowException(
                implode(',', $errors)
            );

            return false;
        }

        return true;
    }

    /**
     * Обновление элемента.
     *
     * @param array $arFields Поля элемента.
     *
     * @return mixed
     * @throws RuntimeException Внутренние ошибки.
     */
    public function onBeforeIBlockElementUpdateHandler(array &$arFields)
    {
        $errors = $errorsLocal = [];

        foreach ($this->config as $item) {
            if ($arFields['IBLOCK_ID'] !== (int)$item['id_iblock']) {
                continue;
            }

            $propertyId = $this->getPropertyIdByCode($item['id_iblock'], $item['code_property']);

            // Неверное свойство в конфиге.
            if (!$propertyId) {
                throw new RuntimeException(
                    sprintf(
                        'Не смог найти свойство %s в инфоблоке %s.',
                        $item['code_property'],
                        $item['id_iblock']
                    )
                );
            }

            $request = Context::getCurrent()->getRequest();

            //  Или свойство есть, а его значений - нет.
            if (!$request->isAdminSection()
                &&
                !array_key_exists($item['code_property'], $arFields['PROPERTY_VALUES'])) {
                throw new RuntimeException(
                    sprintf(
                        'Не смог найти свойство %s в инфоблоке %s.',
                        $item['code_property'],
                        $item['id_iblock']
                    )
                );
            }

            $arValueProperty = $arFields['PROPERTY_VALUES'][$propertyId];
            $keyInArray = array_key_first($arValueProperty);

            // Санация.
            if ($item['sanitize']) {
                $arFields['PROPERTY_VALUES'][$propertyId][$keyInArray]['VALUE'] = $this->sanitize(
                    $arValueProperty[$keyInArray]['VALUE'],
                    $item['sanitize']
                );
            }

            $valueProperty = (array)$arValueProperty[$keyInArray]['VALUE'];

            $resultValidation = $this->doValidate($valueProperty, $item);
            if(count($resultValidation) > 0 ) {
                $errorsLocal[] = $resultValidation;
            }
        }

        $errors = array_merge($errors, ...$errorsLocal);

        if (count($errors) > 0) {
            $GLOBALS['APPLICATION']->ThrowException(
                implode(',', $errors)
            );

            return false;
        }

        return true;
    }

    /**
     * ID свойства по коду.
     *
     * @param integer $iblockId     ID инфоблока.
     * @param string  $propertyCode Символьный код свойства.
     *
     * @return integer
     */
    private function getPropertyIdByCode(int $iblockId, string $propertyCode) : int
    {
        $properties = CIBlockProperty::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'CODE' => $propertyCode]
        );

        if ($fields = $properties->GetNext()) {
            return $fields['ID'];
        }

        return 0;
    }

    /**
     * Валидирует отдельный атрибут.
     *
     * @param mixed  $value        Значение.
     * @param string $rule         Правила валидации в формате Laravel Validator.
     * @param string $errorMessage Сообщение об ошибке.
     *
     * @return boolean
     * @throws ValidateErrorException Ошибки валидации.
     */
    private function validateAttribute($value, string $rule, string $errorMessage): bool
    {
        if ($rule === '') {
            return true;
        }

        $validator = new Validator(
            new Translator(new ArrayLoader(), 'en_US'),
            ['key' => $value],
            ['key' => $rule],
            [$errorMessage]
        );

        if ($validator->fails()) {
            throw new ValidateErrorException(implode(', ', $validator->errors()->all()));
        }

        return true;
    }

    /**
     * @param array $valueProperty Значения свойств.
     * @param array $item          Конфигурация валидатора.
     *
     * @return array
     */
    private function doValidate(array $valueProperty, array $item) : array
    {
        $errors = [];

        foreach ($valueProperty as $property) {
            $errorMessage = str_replace('#FIELD_NAME#', $item['code_property'], $item['error_message']);

            try {
                $this->validateAttribute($property, $item['rule'], $errorMessage);
            } catch (ValidateErrorException $e) {
                $errors[] = $e->getMessage();
            }

            // Вызов кастомного валидатора. При условии, что он существует в контейнере.
            if ($item['optional_validator']
                &&
                $this->customValidators->has((string)$item['optional_validator'])
            ) {
                /** @var BitrixPropertyValidatorInterface $validator */
                $validator = $this->customValidators->get($item['optional_validator']);
                $validator->setPropertyCode($item['code_property']);
                $validator->setIdIblock((int)$item['id_iblock']);

                try {
                    $validator->validate(
                        $property
                    );
                } catch (ValidateErrorException $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return $errors;
    }

    /**
     * Санация.
     *
     * @param mixed  $value Значение.
     * @param string $rule  Правило.
     *
     * @return mixed
     */
    private function sanitize($value, string $rule)
    {
        $sanitizer = $this->sanitizer->make(['key' => $value], ['key' => $rule]);
        $result = $sanitizer->sanitize();

        return $result['key'];
    }
}