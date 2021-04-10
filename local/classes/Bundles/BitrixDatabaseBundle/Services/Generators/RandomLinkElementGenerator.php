<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use CIBlockElement;
use CIBlockResult;
use Exception;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\Abstraction\AbstractGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Iblocks\HighloadBlock;
use RuntimeException;

/**
 * Class LinkElementGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 10.04.2021
 */
class RandomLinkElementGenerator extends AbstractGenerator
{
    /**
     * @var CIBlockElement $ciblockElement Битриксовый CIBlockElement.
     */
    private $ciblockElement;

    /**
     * RandomLinkElementGenerator constructor.
     *
     * @param CIBlockElement $ciblockElement Битриксовый CIBlockElement.
     */
    public function __construct(
        CIBlockElement $ciblockElement
    ) {
        $this->ciblockElement = $ciblockElement;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generate(?array $payload = null)
    {
        if ($payload === null) {
            throw new RuntimeException(
                'Для поля типа привязка к элементам указывать ключ поля обязательно.'
            );
        }

        $arFilter = [];

        // HL блоки.
        if (array_key_exists('hlblock_code', $payload)) {
            $hl = new HighloadBlock();
            $propData = $hl->getPropertyData($payload['hlblock_code'], $payload['field']);

            if ($propData['SETTINGS']['IBLOCK_ID']) {
                $arFilter['IBLOCK_ID'] = $propData['SETTINGS']['IBLOCK_ID'];
            }
        }

        $arFilter = array_merge($arFilter, ['ACTIVE' => 'Y']);

        /** @var CIBlockResult $result */
        $result = $this->ciblockElement::GetList(
            ['RAND' => 'ASC'],
            $arFilter,
            false,
            false,
            [
                'ID',
            ]
        );

        if ($ob = $result->GetNext()) {
            return (int)$ob['ID'];
        }

       return 0;
    }
}
