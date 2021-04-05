<?php

namespace Local\Bundles\BitrixIblockElementValidatorBundle\Services;

use Bitrix\Main\Context;
use CIBlockElement;
use Local\Bundles\BitrixIblockElementValidatorBundle\Services\Exceptions\DuplicatePropertyException;
use RuntimeException;

/**
 * Class BitrixUniquePropertyValidator
 * Не дает сохранить значение, которое уже есть в базе для заданного свойства.
 * @package Local\Bundles\BitrixIblockElementValidatorBundle\Services
 *
 * @since 07.02.2021
 */
class BitrixUniquePropertyValidator extends AbstractBitrixPropertyValidator
{
    /**
     * @var CIBlockElement $blockElement Битриксовый CIBlockElement.
     */
    private $blockElement;

    /**
     * ExampleCustomValidator constructor.
     *
     * @param string         $errorMessage Сообщение об ошибке.
     * @param CIBlockElement $blockElement Битриксовый CIBlockElement.
     */
    public function __construct(
        string $errorMessage,
        CIBlockElement $blockElement
    ) {
        $this->errorMessage = $errorMessage;
        $this->blockElement = $blockElement;
    }

    /**
     * @inheritDoc
     * @throws DuplicatePropertyException
     */
    public function validate($value): bool
    {
        // В админке игнорируем. Также, если пришло пустое значение.
        $request = Context::getCurrent()->getRequest();
        if (!$value || $request->isAdminSection()) {
            return true;
        }

        $result =  $this->has(
            $this->idIblock,
            $this->propertyCode,
            $value
        );

        if (!$result) {
            $errorMessage = str_replace('#FIELD_NAME#', $this->propertyCode, $this->errorMessage);

            throw new DuplicatePropertyException(
                $errorMessage
            );
        }

        return true;
    }

    /**
     * Проверка - есть ли уже в базе запись с таким значением.
     *
     * @param integer $idIblock     ID инфоблока.
     * @param string  $codeProperty Код свойства.
     * @param mixed   $value        Значение.
     *
     * @return boolean
     */
    private function has(int $idIblock, string $codeProperty, $value) : bool
    {
        $rsElement = $this->blockElement::GetList(
            [],
            [
                'ACTIVE' => 'Y',
                'IBLOCK_ID' => $idIblock,
                'PROPERTY_' . $codeProperty => $value
            ],
            false,
            false,
            ['ID']
        );

        if (is_object($rsElement) && $rsElement->Fetch()) {
            return false;
        }

        return true;
    }
}
