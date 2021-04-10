<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use CFile;
use Exception;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;
use Mmo\Faker\PicsumProvider;
use RuntimeException;

/**
 * Class ImageGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 08.04.2021
 */
class ImageGenerator implements FixtureGeneratorInterface
{
    /**
     * @var Generator $faker Фэйкер.
     */
    private $faker;

    /**
     * @var integer $width Ширина картинки.
     */
    private $width;

    /**
     * @var integer $height Высота картинки.
     */
    private $height;

    /**
     * ImageGenerator constructor.
     *
     * @param integer $width      Ширина картинки.
     * @param integer $height     Высота картинки.
     * @param boolean $randomSize Случайная высота и ширина картинки.
     *
     * @throws Exception
     */
    public function __construct(int $width = 800, int $height = 800, bool $randomSize = false)
    {
        $this->faker = Factory::create('ru_Ru');
        $this->faker->addProvider(new PicsumProvider($this->faker));

        $this->width = $width;
        $this->height = $height;
        if ($randomSize) {
            $this->width = random_int(100, $this->width);
            $this->height = random_int(100, $this->height);
        }
    }

    /**
     * @inheritDoc
     * @throws Exception | RuntimeException
     */
    public function generate(?array $payload = null)
    {
        $imageUrl = $this->faker->picsumStaticRandomUrl($this->width, $this->height);

        return $this->generatePhotoFromLink($imageUrl);
    }

    /**
     * @param string $photoLink Ссылка на картинку.
     *
     * @return array
     * @throws RuntimeException
     */
    private function generatePhotoFromLink(string $photoLink): array
    {
        $arPicture = CFile::MakeFileArray($photoLink);
        if (!is_array($arPicture)) {
            throw new RuntimeException('Ошибка подготовки данных изображения.');
        }

        if (!array_key_exists('tmp_name', $arPicture)) {
            throw new RuntimeException('Ошибка сохранения изображения.');
        }

        $arPicture['name'] .= '.jpg';

        return $arPicture;
    }
}
