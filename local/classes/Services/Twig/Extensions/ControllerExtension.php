<?php

namespace Local\Services\Twig\Extensions;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig_ExtensionInterface;

/**
 * Class ControllerExtension
 * @package Local\Services\Twig\Extensions
 *
 * @since 20.10.2020
 */
class ControllerExtension extends AbstractExtension implements Twig_ExtensionInterface
{
    /**
     * Return extension name
     *
     * @return string
     */
    public function getName()
    {
        return 'controller_extension';
    }

    /**
     * {@inheritdoc}
     */
    /**
     * Twig functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('controller', [$this, 'getControllerReference']),
        ];
    }

    public function getControllerReference(string $controller, array $atrributes = [], array $query = [])
    {
        return new ControllerReference($controller, $atrributes, $query);
    }
}
