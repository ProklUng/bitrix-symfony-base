<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 *
 * @since 02.04.2021
 */
class BitrixFile extends Annotation
{
    /** @var string $var Название переменной в Request, куда будет проведена десериализация */
    public $var = '';

    /**
     * @return string
     */
    public function getVar(): string
    {
        return $this->var;
    }
}
