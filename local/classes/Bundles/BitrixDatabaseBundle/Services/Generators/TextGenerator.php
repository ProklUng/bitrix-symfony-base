<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use CUser;
use Exception;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixDatabaseBundle\Services\Contracts\FixtureGeneratorInterface;

/**
 * Class TextGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 08.04.2021
 */
class TextGenerator implements FixtureGeneratorInterface
{
    /**
     * @var Generator $faker Фэйкер.
     */
    private $faker;

    /**
     * @var integer $minLength Минимальная длина текста.
     */
    private $minLength;

    /**
     * @var integer $maxLength Максимальная длина текста.
     */
    private $maxLength;

    /**
     * TextGenerator constructor.
     *
     * @param integer $minLength Минимальная длина текста.
     * @param integer $maxLength Максимальная длина текста.
     */
    public function __construct(int $minLength, int $maxLength)
    {
        $this->faker = Factory::create('ru_RU');

        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generate(?array $payload = null)
    {
        $text = $this->faker->realText($this->maxLength);

        if (strlen($text) < $this->minLength) {
            $text = $this->minLength >= 5 ? $this->faker->realText($this->minLength)
                :
                $this->generateRandomString($this->minLength)
            ;
        }

        return $text;
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