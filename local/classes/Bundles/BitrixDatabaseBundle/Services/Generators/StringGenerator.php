<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use Exception;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\Abstraction\AbstractGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\Traits\StringUtilsTrait;

/**
 * Class StringGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 08.04.2021
 */
class StringGenerator extends AbstractGenerator
{
    use StringUtilsTrait;

    /**
     * @var Generator $faker Фэйкер.
     */
    private $faker;

    /**
     * @var integer $minLength Минимальная длина строки.
     */
    private $minLength;

    /**
     * @var integer $maxLength Максимальная длина строки.
     */
    private $maxLength;

    /**
     * StringGenerator constructor.
     *
     * @param integer $minLength Минимальная длина текста.
     * @param integer $maxLength Максимальная длина текста.
     */
    public function __construct(int $minLength = 1, int $maxLength = 255)
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
        if (array_key_exists('length', $payload['params'])) {
            $text = $this->faker->sentence($payload['params']['length']);
            return substr($text, 0, $payload['params']['length']);
        }

        if (array_key_exists('length', $this->params)
            ||
            $this->maxLength === $this->minLength
        ) {
            $text = $this->faker->sentence((int)$this->params['length']);
            return substr($text, 0, (int)$this->params['length']);
        }

        $text = $this->faker->sentence($this->maxLength ?: 20);

        if (strlen($text) > $this->maxLength) {
            $text = substr($text, 0, $this->maxLength);
        }

        if (strlen($text) < $this->minLength) {
            $text = $this->generateRandomString(random_int(1, $this->minLength));
        }

        return $text;
    }
}
