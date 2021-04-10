<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Type\DateTime;
use Exception;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class FixtureGenerator
 * Генератор фикстур для кастомных таблиц.
 * @package Local\Bundles\BitrixDatabaseBundle\Services
 *
 * @since 08.04.2021
 *
 */
class FixtureGenerator
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
     * @var array $fixturePaths
     */
    private $fixturePaths;

    /**
     * @var array $fixtureSchema
     */
    private $fixtureSchema = [];

    /**
     * FixtureGenerator constructor.
     *
     * @param ServiceLocator $locator      Сервисы, помеченные тэгом fixture_generator.item.
     * @param array          $fixturePaths Пути к фикстурам.
     */
    public function __construct(
        ServiceLocator $locator,
        array $fixturePaths = []
    ) {
        $this->locator = $locator;
        $this->fixturePaths = $fixturePaths;
        $this->faker = Factory::create('ru_Ru');
    }

    /**
     * @param DataManager $schema Схема.
     * @param integer     $count  Количество элементов.
     *
     * @return array
     * @throws RuntimeException | Exception
     */
    public function fromSchema(DataManager $schema, int $count = 1) : array
    {
        if ($count < 0) {
            throw new RuntimeException(
                'Количество запрашиваемых фикстур не может быть меньше нуля.'
            );
        }

        $this->fixtureSchema = $this->loadFixtureFromFile($schema::getTableName());

        $tableDescription = $schema::getTableDescription();
        $result = [];

        for ($i = 1; $i<= $count; $i++) {
            $result[] = $this->getFixtureItemFromDescription($tableDescription);
        }

        return $result;
    }

    /**
     * @param string $tableName Название таблицы.
     *
     * @return array
     */
    private function loadFixtureFromFile(string $tableName) : array
    {
        foreach ($this->fixturePaths as $path) {
            $pathFile = $_SERVER['DOCUMENT_ROOT'] . $path . $tableName . '.php';
            if (!@file_exists($pathFile)) {
                continue;
            }
            $result = include $_SERVER['DOCUMENT_ROOT'] . $this->fixturePaths[0] . $tableName . '.php';
            if (is_array($result)) {
                return $result;
            }
        }

        return [];
    }

    /**
     * Строка БД для фикстуры.
     *
     * @param array $schema Схема.
     *
     * @return array
     * @throws RuntimeException | InvalidArgumentException | Exception
     */
    private function getFixtureItemFromDescription(array $schema) : array
    {
        $result = [];
        /** @var array $fieldData */
        foreach ($schema as $fieldData) {
            if ($fieldData['COLUMN_KEY'] === 'PRI') {
                continue;
            }

            $nameField = $fieldData['COLUMN_NAME'];

            // Поиск в фикстуре с диска.
            if (array_key_exists($nameField, $this->fixtureSchema)) {
                $serviceId = $this->fixtureSchema[$nameField];
                if ($this->locator->has($serviceId)) {
                    /** @var FixtureGeneratorInterface $generator */
                    $generator = $this->locator->get($serviceId);
                    $payload = ['field' => $nameField];
                    $result[$nameField] = $generator->generate($payload);
                    continue;
                }

                // Если в фикстуре не сервис, то использовать значение.
                $result[$nameField] = $this->fixtureSchema[$nameField];
                continue;
            }

            $typeField = $fieldData['DATA_TYPE'];
            $length = (int)$fieldData['CHARACTER_MAXIMUM_LENGTH'];

            if ($typeField === 'varchar') {
                // Поля с описанием типов других полей
                if (stripos($nameField, '_TEXT_TYPE') !== false) {
                    $randomType = random_int(0, 1);
                    $result[$nameField] = $randomType ? 'html' : 'text';
                    continue;
                }

                // Поля типа ACTIVE.
                if ($length === 1) {
                    $result[$nameField] = $this->generateRandomString($length, 'YN');
                    continue;
                }

                if ($length < 5) {
                    $result[$nameField] = $this->generateRandomString($length);
                    continue;
                }

                $result[$nameField] = $length >=5 ? $this->faker->text($length)
                    : $this->generateRandomString($length);
                continue;
            }

            if ($typeField === 'timestamp') {
                $result[$nameField] = DateTime::createFromUserTime(
                    $this->faker->dateTimeThisYear->format('d.m.Y H:i:s')
                );
                continue;
            }

            if ($typeField === 'datetime') {
                $result[$nameField] = DateTime::createFromUserTime(
                    $this->faker->dateTimeThisYear->format('d.m.Y H:i:s')
                );
                continue;
            }

            if ($typeField === 'longtext') {
                $length = $length ?: 65000;
                $result[$nameField] = $length >=5 ? $this->faker->text($length)
                    : $this->generateRandomString($length);

                continue;
            }

            if ($typeField === 'tinytext') {
                if (!$length) {
                    $length = 255;
                }
                $result[$nameField] = $this->faker->realText($length);
                continue;
            }

            if ($typeField === 'mediumtext') {
                $length = $length ?: 65000;
                $result[$nameField] = $length >=5 ? $this->faker->realText($length)
                    : $this->generateRandomString($length);

                continue;
            }

            if ($typeField === 'text') {
                $length = $length ?: 65000;
                $result[$nameField] = $length >=5 ? $this->faker->realText($length)
                    : $this->generateRandomString($length);
                continue;
            }

            if ($typeField === 'boolean') {
                $result[$nameField] = (string)$this->faker->boolean();
                continue;
            }

            if ($typeField === 'int') {
                $result[$nameField] = $this->faker->numberBetween(1, 65000);
                continue;
            }

            if ($typeField === 'tinyInt') {
                $result[$nameField] = $this->faker->numberBetween(1, 127);
                continue;
            }

            if ($typeField === 'bigInt') {
                $result[$nameField] = $this->faker->numberBetween(1, 12147483647);
                continue;
            }
        }

        return $result;
    }

    /**
     * Случайная строка (Фэйкер отказывается генерить строки меньше 5 символов длиной).
     *
     * @param integer $length Длина нужной строки.
     * @param string  $src    Альтернативный набор символов.
     *
     * @return string
     * @throws Exception
     */
    private function generateRandomString(int $length = 25, string $src = '') : string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($src !== '') {
            $characters = $src;
        }

        $charactersLength = strlen($characters);

        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
