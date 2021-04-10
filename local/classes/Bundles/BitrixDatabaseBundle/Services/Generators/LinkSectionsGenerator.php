<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use CIBlockElement;
use CIBlockSection;
use Exception;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;
use Local\Bundles\BitrixDatabaseBundle\Services\Iblocks\IblockProperties;
use RuntimeException;

/**
 * Class LinkSectionsGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 10.04.2021
 */
class LinkSectionsGenerator implements FixtureGeneratorInterface
{
    /**
     * @var IblockProperties $propertiesManager Менеджер свойств инфоблоков.
     */
    private $propertiesManager;

    /**
     * @var CIBlockSection $ciblockSection Битриксовый CIBlockElement.
     */
    private $ciblockSection;

    /**
     * LinkSectionsGenerator constructor.
     *
     * @param IblockProperties $propertiesManager Менеджер свойств инфоблоков.
     * @param CIBlockSection   $ciblockSection    Битриксовый CIBlockElement.
     */
    public function __construct(
        IblockProperties $propertiesManager,
        CIBlockSection $ciblockSection
    ) {
        $this->propertiesManager = $propertiesManager;
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
                'Для поля типа привязка к элементам указывать ключ поля обязательно.'
            );
        }

        $fieldData = $this->propertiesManager->getProperty(
            $payload['iblock_id'],
            $payload['field'],
        );

        $linkedIblock = $fieldData['LINK_IBLOCK_ID'] ?? null;
        if (!$linkedIblock) {
            return null;
        }

        $result = $this->ciblockSection::GetList(
            [],
            ['ACTIVE' => 'Y', 'IBLOCK_ID' => $linkedIblock],
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
