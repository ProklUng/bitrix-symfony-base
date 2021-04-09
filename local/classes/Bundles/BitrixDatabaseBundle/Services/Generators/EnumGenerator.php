<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use Exception;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;
use Local\Bundles\BitrixDatabaseBundle\Services\Iblocks\IblockProperties;
use RuntimeException;

/**
 * Class EnumGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 09.04.2021
 */
class EnumGenerator implements FixtureGeneratorInterface
{
    /**
     * @var IblockProperties $propertiesManager Менеджер свойств инфоблоков.
     */
    private $propertiesManager;

    /**
     * EnumGenerator constructor.
     *
     * @param IblockProperties $propertiesManager Менеджер свойств инфоблоков.
     */
    public function __construct(IblockProperties $propertiesManager)
    {
        $this->propertiesManager = $propertiesManager;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generate(?array $payload = null)
    {
        if ($payload === null) {
            throw new RuntimeException(
                'Для поля типа список указывать ключ поля обязательно.'
            );
        }

        $fieldData = $this->propertiesManager->getPropertyEnumValuesByCode(
            $payload['iblock_id'],
            $payload['field'],
        );

        $randomKey = random_int(0, count($fieldData) -1);

        return $fieldData[$randomKey]['ID'];
    }
}
