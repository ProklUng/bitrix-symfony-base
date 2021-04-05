<?php

namespace Local\Services\Twig\Extensions;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Class GlobalsExtension
 * Глобальные переменные. Секция globals конфигурации twig.
 * @package Local\Services\Twig\Extensions
 *
 * @since 17.02.2021
 */
class GlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    use ContainerAwareTrait;

    /**
     * @var array $config Конфигурация. Секция twig.
     */
    private $config;

    /**
     * GlobalsExtension constructor.
     *
     * @param array $config Конфигурация.
     */
    public function __construct(
        array $config
    ) {
        $this->config = array_key_exists('globals', $config)
                        ?
                        $config['globals']
                        :
                        [];
    }

    /**
     * Return extension name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'twig/globals';
    }

    /**
     * @inheritDoc
     */
    public function getGlobals() : array
    {
        $result = [];

        foreach ($this->config as $name => $global) {
            if ($this->container->has($global)) {
                $result[$name] = $this->container->get(
                    $global
                );

                continue;
            }

            $result[$name] = $global;
        }

        return $result;
    }
}
