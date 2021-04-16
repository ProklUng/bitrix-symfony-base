<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use Exception;
use Faker\Factory;
use Faker\Generator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\Abstraction\AbstractGenerator;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\Traits\StringUtilsTrait;

/**
 * Class TextGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 08.04.2021
 */
class TextGenerator extends AbstractGenerator
{
    use StringUtilsTrait;

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
        if (array_key_exists('length', $this->params)
            ||
            $this->maxLength === $this->minLength
        ) {
            $text = $this->faker->realText((int)$this->params['length']);
            return substr($text, 0, (int)$this->params['length']);
        }

        $text = $this->faker->realText($this->maxLength);

        if (strlen($text) < $this->minLength) {
            $text = $this->minLength >= 5 ? $this->faker->realText($this->minLength)
                :
                $this->generateRandomString($this->minLength)
            ;
        }

        return $text;
    }
}
