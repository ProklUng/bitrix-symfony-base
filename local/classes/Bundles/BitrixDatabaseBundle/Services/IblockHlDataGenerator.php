<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;
use Local\Bundles\BitrixDatabaseBundle\Services\Iblocks\HighloadBlock;
use Local\Bundles\BitrixDatabaseBundle\Services\Traits\DataGeneratorTrait;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class IblockHlDataGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services
 *
 * @since 10.04.2021
 */
class IblockHlDataGenerator
{
    use DataGeneratorTrait;

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
     * @var HighloadBlock $highloadBlock
     */
    private $highloadBlock;

    /**
     * @var DefaultPropertiesValueProcessor $elementMapper
     */
    private $elementMapper;

    /**
     * @var boolean $ignoreErrors Игнорировать ошибки.
     */
    private $ignoreErrors;

    /**
     * IblockHlDataGenerator constructor.
     *
     * @param ServiceLocator                  $locator       Сервисы, помеченные тэгом fixture_generator.item.
     * @param DefaultPropertiesValueProcessor $elementMapper Маппер по умолчанию.
     * @param HighloadBlock                   $highloadBlock High-load block manager.
     * @param boolean                         $ignoreErrors  Игнорировать ошибки.
     * @param array                           $fixturePaths  Пути к фикстурам.
     */
    public function __construct(
        ServiceLocator $locator,
        DefaultPropertiesValueProcessor $elementMapper,
        HighloadBlock $highloadBlock,
        bool $ignoreErrors = false,
        array $fixturePaths = []
    ) {
        $this->locator = $locator;
        $this->fixturePaths = $fixturePaths;
        $this->highloadBlock = $highloadBlock;
        $this->elementMapper = $elementMapper;
        $this->ignoreErrors = $ignoreErrors;
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
        $result = [];

        // Поля по умолчанию.
        $propData = $this->highloadBlock->getAllProperties($payload['hlblock_code']);
        $defaultProps = $this->elementMapper->getMapHl($propData);

        // Поля из фикстуры.
        // Принцип именования файла с фикстурой: код hl-блока.php.
        $fixtureSchema = $this->loadFixtureFromFile($this->fixturePaths, $this->iblockCode);
        $resultSchema = array_merge($defaultProps, $fixtureSchema);

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
     * Разрешить генератор фикстурных данных из сервис-локатора.
     *
     * @param array  $data       Данные.
     * @param string $iblockCode ID инфоблока.
     *
     * @return array
     */
    private function resolveGeneratorsFromLocator(array $data, string $iblockCode) : array
    {
        $result = [];

        foreach ($data as $nameField => $item) {
            /** @var string $serviceId */
            $serviceId = $data[$nameField];
            if ($this->locator->has($serviceId)) {
                /** @var FixtureGeneratorInterface $generator */
                $generator = $this->locator->get($serviceId);
                $payload = ['field' => $nameField, 'hlblock_code' => $iblockCode, 'ignore_errors' => $this->ignoreErrors];
                $result[$nameField] = $generator->generate($payload);
                continue;
            }

            // Если в фикстуре не сервис, то использовать значение.
            $result[$nameField] = $data[$nameField];
        }

        return $result;
    }
}
