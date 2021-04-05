<?php

namespace Local\Services\Twig\Extensions;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Twig\Extension\AbstractExtension;
use Twig_ExtensionInterface;
use Twig_SimpleFunction;

/**
 * Class ContainerTwigExtension
 * Container extension.
 * @package Local\Services\Twig\Extensions
 *
 * @since 11.10.2020
 */
class ContainerTwigExtension extends AbstractExtension implements Twig_ExtensionInterface
{
    use ContainerAwareTrait;

    /**
     * Return extension name.
     *
     * @return string
     */
    public function getName()
    {
        return 'twig/container';
    }

    /**
     * Функции.
     *
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('service', [$this, 'service']),
            new Twig_SimpleFunction('param', [$this, 'param']),
        );
    }

    /**
     * Вызов сервиса.
     *
     * @param string $service Сервис.
     *
     * @return mixed
     */
    public function service(string $service)
    {
        return $this->container->get($service);
    }

    /**
     * Параметр контейнера.
     *
     * @param string $param Переменная.
     *
     * @return mixed
     */
    public function param(string $param)
    {
        return $this->container->getParameter($param);
    }
}