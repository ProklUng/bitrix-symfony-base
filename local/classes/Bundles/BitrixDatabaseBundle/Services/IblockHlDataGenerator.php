<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;
use Local\Bundles\BitrixDatabaseBundle\Services\Iblocks\HighloadBlock;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class IblockHlDataGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services
 *
 * @since 10.04.2021
 */
class IblockHlDataGenerator
{
    /**
     * @var ServiceLocator $locator Сервисы, помеченные тэгом fixture_generator.item.
     */
    private $locator;

    /**
     * @var string $iblockCode Код инфоблока.
     */
    private $iblockCode;

    /**
     * @var array $fixturePaths
     */
    private $fixturePaths;

    /**
     * @var array $fixtureSchema
     */
    private $fixtureSchema = [];

    /**
     * @var HighloadBlock $highloadBlock
     */
    private $highloadBlock;

    /**
     * @var DefaultPropertiesValueProcessor $elementMapper
     */
    private $elementMapper;

    /**
     * IblockHlDataGenerator constructor.
     *
     * @param ServiceLocator                  $locator       Сервисы, помеченные тэгом fixture_generator.item.
     * @param DefaultPropertiesValueProcessor $elementMapper Маппер по умолчанию.
     * @param HighloadBlock                   $highloadBlock High-load block manager.
     * @param array                           $fixturePaths  Пути к фикстурам.
     */
    public function __construct(
        ServiceLocator $locator,
        DefaultPropertiesValueProcessor $elementMapper,
        HighloadBlock $highloadBlock,
        array $fixturePaths = []
    ) {
        $this->locator = $locator;
        $this->fixturePaths = $fixturePaths;
        $this->highloadBlock = $highloadBlock;
        $this->elementMapper = $elementMapper;
    }

    /**
     * @param array $payload Нагрузка.
     *
     * @return array
     *
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    public function generate(array $payload = []) : array
    {
        $propData = $this->highloadBlock->getAllProperties($payload['iblock_code']);
        $defaultProps = $this->elementMapper->getMapHl($propData);

        $this->fixtureSchema = $this->loadFixtureFromFile($this->iblockCode);
        $resultSchema = array_merge($defaultProps, $this->fixtureSchema);

        $resultFixture = $this->resolveGeneratorsFromLocator($resultSchema, $this->iblockCode);

        $result[] = $this->addElement($this->iblockCode, $resultFixture);

        return $result;
    }

    /**
     * @param string $iblockCode Код инфоблока.
     *
     * @return IblockHlDataGenerator
     */
    public function setIblockCode(string $iblockCode): self
    {
        $this->iblockCode = $iblockCode;

        return $this;
    }

    /**
     * Добавляет элемент инфоблока.
     *
     * @param string $code   ID инфоблока.
     * @param array  $fields Поля.
     *
     * @return integer
     * @throws ArgumentException | ObjectPropertyException | SystemException
     */
    private function addElement(string $code, array $fields = []): int
    {
        return $this->highloadBlock->addElement(
            $code,
            $fields
        );
    }

    /**
     * @param array  $data       Данные.
     * @param string $iblockCode ID инфоблока.
     *
     * @return array
     */
    private function resolveGeneratorsFromLocator(array $data, string $iblockCode) : array
    {
        $result = [];

        foreach ($data as $nameField => $item) {
            $serviceId = $data[$nameField];
            if ($this->locator->has($serviceId)) {
                /** @var FixtureGeneratorInterface $generator */
                $generator = $this->locator->get($serviceId);
                $payload = ['field' => $nameField, 'iblock_code' => $iblockCode];
                $result[$nameField] = $generator->generate($payload);
                continue;
            }

            // Если в фикстуре не сервис, то использовать значение.
            $result[$nameField] = $data[$nameField];
        }

        return $result;
    }

    /**
     * @param string $fileName Название таблицы.
     *
     * @return array
     */
    private function loadFixtureFromFile(string $fileName) : array
    {
        foreach ($this->fixturePaths as $path) {
            $pathFile = $_SERVER['DOCUMENT_ROOT'] . $path . $fileName . '.php';
            if (!@file_exists($pathFile)) {
                continue;
            }
            $result = include $_SERVER['DOCUMENT_ROOT'] . $this->fixturePaths[0] . $fileName . '.php';
            if (is_array($result)) {
                return $result;
            }
        }

        return [];
    }
}
