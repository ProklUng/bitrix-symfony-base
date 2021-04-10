<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use CIBlockSection;
use Exception;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\Abstraction\AbstractGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Iblocks\HighloadBlock;
use RuntimeException;

/**
 * Class RandomLinkSectionGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 10.04.2021
 */
class RandomLinkSectionGenerator extends AbstractGenerator
{
    /**
     * @var CIBlockSection $ciblockSection Битриксовый CIBlockElement.
     */
    private $ciblockSection;

    /**
     * RandomLinkSectionGenerator constructor.
     *
     * @param CIBlockSection $ciblockSection Битриксовый CIBlockSection.
     */
    public function __construct(
        CIBlockSection $ciblockSection
    ) {
        $this->ciblockSection = $ciblockSection;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generate(?array $payload = null)
    {
        if ($payload === null) {
            throw new RuntimeException(
                'Для поля типа привязка к разделам указывать ключ поля обязательно.'
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

        $result = $this->ciblockSection::GetList(
            [],
            $arFilter,
            false,
            [
                'ID',
            ]
        );

        $sections = [];
        while ($ob = $result->GetNext()) {
            $sections[] = $ob['ID'];
        }

        if (count($sections) === 0) {
            return 0;
        }

        $random = mt_rand(0, count($sections) - 1);

        return $sections[$random];
    }
}
