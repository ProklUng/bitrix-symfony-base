<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 *
 * @since 01.04.2021
 */
class QueryParams extends Annotation
{
    /** @var string Класс, в экземпляр которого будет проведена десериализация Request. */
    public $class = '';

    /** @var string $var Название переменной в Request, куда будет проведена десериализация */
    public $var = '';

    /**
     * @var boolean $validate Валидация.
     */
    public $validate = true;

    /**
     * @return boolean
     */
    public function isValidate(): bool
    {
        return $this->validate;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getVar(): string
    {
        return $this->var;
    }
}
