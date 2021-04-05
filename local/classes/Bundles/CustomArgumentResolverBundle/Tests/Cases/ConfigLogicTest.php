<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases;

use Exception;
use Local\Bundles\CustomArgumentResolverBundle\DependencyInjection\CustomArgumentResolverBundleExtension;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ConfigLogicTest
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Cases
 *
 * @since 05.12.2020
 */
class ConfigLogicTest extends BaseTestCase
{
    /**
     * @var CustomArgumentResolverBundleExtension $testObject Тестовый объект.
     */
    protected $testObject;

    /**
     * @var ContainerBuilder $container
     */
    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'dev');

        $this->testObject = new CustomArgumentResolverBundleExtension();
    }

    /**
     * load(). enabled = false.
     *
     * @param string $serviceId
     *
     * @throws Exception
     *
     * @dataProvider dataProviderValidServiceId
     */
    public function testLoadDisabledBundle(string $serviceId): void
    {
        $this->testObject->load(
            [
                [
                    'defaults' => [
                        'enabled' => false,
                    ],
                ],
            ],
            $this->container
        );

        $hasDef = $this->container->hasDefinition($serviceId);
        $this->assertFalse(
            $hasDef,
            'Проскочил сервис, которого в контейнере быть не должно.'
        );
    }

    /**
     * load(). enabled = true.
     *
     * @param string $serviceId
     *
     * @throws Exception
     *
     * @dataProvider dataProviderValidServiceId
     */
    public function testLoadEnabledBundle(string $serviceId): void
    {
        $this->testObject->load(
            [
                [
                    'defaults' => [
                        'enabled' => true,
                    ],
                ],
            ],
            $this->container
        );

        $hasDef = $this->container->hasDefinition($serviceId);
        $this->assertTrue(
            $hasDef,
            'Отсутствует сервис, который должен оказаться в контейнере.'
        );
    }

    /**
     * Датапровайдер валидных ID сервисов бандла.
     *
     * @return string[][]
     */
    public function dataProviderValidServiceId(): array
    {
        return [
            ['custom_arguments_resolvers.resolver.from.container'],
            ['custom_arguments_resolvers.argument_resolver.set_container'],
            ['custom_arguments_resolvers.argument_resolver.params'],
            ['custom_arguments_resolvers.argument_resolver.ajax_call'],
            ['custom_arguments_resolvers.controller_argument.processor'],
            ['custom_arguments_resolvers.argument_resolver.from_container'],
            ['custom_arguments_resolvers.boot_trait'],
            ['custom_arguments_resolvers.container.aware.resolver'],
            ['custom_arguments_resolvers.resolver'],
            ['custom_arguments_resolvers.ignored.autowiring.controller.arguments'],
        ];
    }
}
