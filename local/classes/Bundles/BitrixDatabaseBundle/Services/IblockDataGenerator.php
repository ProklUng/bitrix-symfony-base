<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services;

use Bitrix\Main\ArgumentException;
use CIBlock;
use CIBlockElement;
use Exception;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;
use Local\Bundles\BitrixDatabaseBundle\Services\Iblocks\IblockSections;
use RuntimeException;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class IblockDataGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services
 *
 * @since 08.04.2021
 */
class IblockDataGenerator
{
    /**
     * @var Generator $faker Фэйкер.
     */
    private $faker;

    /**
     * @var ServiceLocator $locator Сервисы, помеченные тэгом fixture_generator.item.
     */
    private $locator;

    /**
     * @var IblockSections $iblockSections Работа с подразделами.
     */
    private $iblockSections;

    /**
     * @var string $iblockCode Код инфоблока.
     */
    private $iblockCode;

    /**
     * @var string $iblockType Тип инфоблока.
     */
    private $iblockType;

    /**
     * @var integer $iblockId
     */
    private $iblockId;

    /**
     * @var array $fixturePaths
     */
    private $fixturePaths;

    /**
     * @var array $fixtureSchema
     */
    private $fixtureSchema = [];

    /**
     * @var array $elementMapper
     */
    private $elementMap;

    /**
     * @var array $sectionMap
     */
    private $sectionMap;

    /**
     * IblockDataGenerator constructor.
     *
     * @param ServiceLocator           $locator        Сервисы, помеченные тэгом fixture_generator.item.
     * @param StandartIblockDataMapper $elementMapper  Маппер по умолчанию.
     * @param IblockSections           $iblockSections Работа с подразделами.
     * @param array                    $fixturePaths   Пути к фикстурам.
     */
    public function __construct(
        ServiceLocator $locator,
        StandartIblockDataMapper $elementMapper,
        IblockSections $iblockSections,
        array $fixturePaths = []
    ) {
        $this->locator = $locator;
        $this->fixturePaths = $fixturePaths;
        $this->iblockSections = $iblockSections;

        $this->faker = Factory::create('ru_Ru');
        $this->elementMap = $elementMapper->getMap();
        $this->sectionMap = $elementMapper->getSectionMap();
    }

    /**
     * @param array $sectionsId ID подразделов.
     *
     * @return array
     * @throws ArgumentException | Exception
     */
    public function generate(array $sectionsId = []) : array
    {
        $this->iblockId = $this->getIdIblock($this->iblockCode, $this->iblockType);
        $this->fixtureSchema = $this->loadFixtureFromFile($this->iblockType . '.' . $this->iblockCode);

        $result = $this->resolveGeneratorsFromLocator($this->elementMap, $this->iblockId);
        $resultFixture = $this->resolveGeneratorsFromLocator($this->fixtureSchema, $this->iblockId);

        $result = array_merge($result, $resultFixture);

        if (count($sectionsId) > 0) {
            $randomSectionKey = random_int(0, count($sectionsId)-1);
            $result['IBLOCK_SECTION_ID'] = $sectionsId[$randomSectionKey];
        }

        $result[] = $this->addElement($this->iblockId, $result);

        return $result;
    }

    /**
     * Сгенерировать подразделы.
     *
     * @param integer $count Количество подразделов.
     *
     * @return array
     * @throws ArgumentException
     */
    public function generateSections(int $count) : array
    {
        $result = [];
        $this->iblockId = $this->getIdIblock($this->iblockCode, $this->iblockType);

        for ($i = 1; $i<= $count; $i++) {
            $generators = $this->resolveGeneratorsFromLocator($this->sectionMap, $this->iblockId);
            $result[] = $this->iblockSections->addSection(
                $this->iblockId,
                $generators
            );
        }

        return $result;
    }

    /**
     * Удалить все подразделы.
     *
     * @return void
     * @throws ArgumentException
     */
    public function deleteAllSections() : void
    {
        $this->iblockId = $this->getIdIblock($this->iblockCode, $this->iblockType);
        $this->iblockSections->deleteAllSections($this->iblockId);
    }

    /**
     * ID инфоблока по коду.
     *
     * @param string $iblockCode Код инфоблока.
     * @param string $iblockType Тип инфоблока.
     *
     * @return integer
     *
     * @throws ArgumentException
     */
    public function getIdIblock(string $iblockCode, string $iblockType) : int
    {
        $query = CIBlock::GetList(
            [],
            ['ACTIVE' => 'Y', 'TYPE' => $iblockType, 'CODE' => $iblockCode]
        );

        $arResult = $query->Fetch();
        if ($arResult['ID'] > 0) {
            return $arResult['ID'];
        }

        throw new ArgumentException('Инфоблок '.$iblockCode.' не найден', $iblockCode);
    }

    /**
     * @param string $iblockCode Код инфоблока.
     *
     * @return IblockDataGenerator
     */
    public function setIblockCode(string $iblockCode): IblockDataGenerator
    {
        $this->iblockCode = $iblockCode;

        return $this;
    }

    /**
     * @param string $iblockType Тип инфоблока.
     *
     * @return IblockDataGenerator
     */
    public function setIblockType(string $iblockType): IblockDataGenerator
    {
        $this->iblockType = $iblockType;

        return $this;
    }

    /**
     * Добавляет элемент инфоблока.
     *
     * @param integer $iblockId ID инфоблока.
     * @param array   $fields   Поля.
     * @param array   $props    Свойства.
     *
     * @return integer
     * @throws RuntimeException
     */
    private function addElement(int $iblockId, array $fields = [], array $props = []): int
    {
        $default = [
            'NAME'              => 'element',
            'IBLOCK_SECTION_ID' => false,
            'ACTIVE'            => 'Y',
            'PREVIEW_TEXT'      => '',
            'DETAIL_TEXT'       => '',
        ];

        $fields = array_replace_recursive($default, $fields);
        $fields['IBLOCK_ID'] = $iblockId;

        if (!empty($props)) {
            $fields['PROPERTY_VALUES'] = $props;
        }

        $fields = $this->decodeQuotes($fields);

        $ib = new CIBlockElement;
        $id = $ib->Add($fields);

        if ($id) {
            return (int)$id;
        }

        throw new RuntimeException($ib->LAST_ERROR);
    }

    /**
     * Преобразовать кавычки из сущностей в символ.
     *
     * @param array $data Данные.
     *
     * @return array
     */
    private function decodeQuotes(array $data) : array
    {
        $result = [];
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $result[$key] = $this->decodeQuotes($item);
            } else {
                $result[$key] =  htmlspecialchars_decode($item);
            }
        }

        return $result;
    }


    /**
     * @param array   $data     Данные.
     * @param integer $iblockId ID инфоблока.
     * @return array
     */
    private function resolveGeneratorsFromLocator(array $data, int $iblockId) : array
    {
        $result = [];

        foreach ($data as $nameField => $item) {
            $serviceId = $data[$nameField];
            if ($this->locator->has($serviceId)) {
                /** @var FixtureGeneratorInterface $generator */
                $generator = $this->locator->get($serviceId);
                $payload = ['field' => $nameField, 'iblock_id' => $iblockId];
                $result[$nameField] = $generator->generate($payload);
                continue;
            }

            // Если в фикстуре не сервис, то использовать значение.
            $result[$nameField] = $data[$nameField];
        }

        if (array_key_exists('PROPERTY_VALUES', $data) && is_array($data['PROPERTY_VALUES'])) {
            $result['PROPERTY_VALUES'] = $this->resolveGeneratorsFromLocator($data['PROPERTY_VALUES'], $iblockId);
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