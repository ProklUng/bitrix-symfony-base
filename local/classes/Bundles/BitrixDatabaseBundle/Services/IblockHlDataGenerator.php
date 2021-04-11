<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;
use Local\Bundles\BitrixDatabaseBundle\Services\Iblocks\HighloadBlock;
use Local\Bundles\BitrixDatabaseBundle\Services\Utils\FixtureResolver;
use ReflectionException;
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
     * @var FixtureResolver $fixtureResolver Ресолвер фикстур.
     */
    private $fixtureResolver;

    /**
     * IblockHlDataGenerator constructor.
     *
     * @param ServiceLocator                  $locator         Сервисы, помеченные тэгом fixture_generator.item.
     * @param DefaultPropertiesValueProcessor $elementMapper   Маппер по умолчанию.
     * @param HighloadBlock                   $highloadBlock   High-load block manager.
     * @param FixtureResolver                 $fixtureResolver Ресолвер фикстур.
     * @param boolean                         $ignoreErrors    Игнорировать ошибки.
     */
    public function __construct(
        ServiceLocator $locator,
        DefaultPropertiesValueProcessor $elementMapper,
        HighloadBlock $highloadBlock,
        FixtureResolver $fixtureResolver,
        bool $ignoreErrors = false
    ) {
        $this->locator = $locator;
        $this->highloadBlock = $highloadBlock;
        $this->elementMapper = $elementMapper;
        $this->fixtureResolver = $fixtureResolver;
        $this->ignoreErrors = $ignoreErrors;
    }

    /**
     * @param array $payload Нагрузка.
     *
     * @return array
     *
     * @throws ArgumentException | ObjectPropertyException | SystemException
     * @throws ReflectionException
     */
    public function generate(array $payload = []) : array
    {
        $result = [];

        // Поля по умолчанию.
        $propData = $this->highloadBlock->getAllProperties($payload['hlblock_code']);
        $defaultProps = $this->elementMapper->getMapHl($propData);

        // Поля из фикстуры.
        // Принцип именования файла с фикстурой: код hl-блока.php.
        $fixtureSchema = $this->fixtureResolver->resolve($this->iblockCode);
        $fixtureParams = $this->fixtureResolver->getResolvedParams();

        $resultSchema = array_merge($defaultProps, $fixtureSchema);

        $resultFixture = $this->resolveGeneratorsFromLocator($resultSchema, $this->iblockCode, $fixtureParams);

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
     * @param array  $params     Параметры из аннотации фикстуры.
     *
     * @return array
     */
    private function resolveGeneratorsFromLocator(array $data, string $iblockCode, array $params = []) : array
    {
        $result = [];

        foreach ($data as $nameField => $item) {
            /** @var string $serviceId */
            $serviceId = $data[$nameField];
            if ($this->locator->has($serviceId)) {
                /** @var FixtureGeneratorInterface $generator */
                $generator = $this->locator->get($serviceId);

                $payload = [
                    'field' => $nameField,
                    'hlblock_code' => $iblockCode,
                    'ignore_errors' => $this->ignoreErrors,
                    // Применение параметров из аннотации фикстуры.
                    'params' => array_key_exists($nameField, $params) ? $params[$nameField] : []
                ];

                $result[$nameField] = $generator->generate($payload);
                continue;
            }

            // Если в фикстуре не сервис, то использовать значение.
            $result[$nameField] = $data[$nameField];
        }

        return $result;
    }
}
