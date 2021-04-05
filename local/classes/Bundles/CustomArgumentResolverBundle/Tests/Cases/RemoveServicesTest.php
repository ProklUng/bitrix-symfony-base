<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases;

use Local\Bundles\CustomArgumentResolverBundle\DependencyInjection\CompilerPass\RemoveServices;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Class RemoveServicesTest
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Cases
 *
 * @since 05.12.2020
 */
class RemoveServicesTest extends BaseTestCase
{
    /**
     * @var RemoveServices $testObject Тестовый объект.
     */
    protected $testObject;

    /**
     * @var ContainerBuilder $container
     */
    private $container;

    private $fakeService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new ContainerBuilder();

        $this->testObject = new RemoveServices();

        $this->fakeService = new class {
            public function handle(ControllerEvent $controllerEvent)
            {

            }
        };
    }

    /**
     * process(). Пустой конфиг. Пустая секция конфига disabled_resolvers.
     *
     * @dataProvider dataProviderConfigSectionDisabledService
     *
     * @param array $configs
     * @return void
     */
    public function testProcessEmptyConfig($configs): void
    {
        $this->container->setParameter(
            'custom_arguments_resolvers',
            null
        );

        $this->container->setParameter(
            'custom_arguments_resolvers',
            $configs
        );

        $this->container->register(
            'test.kernel.listener',
            get_class($this->fakeService)
        );

        $this->testObject->process($this->container);
        $def = $this->container->hasDefinition('test.kernel.listener');

        $this->assertTrue(
            $def,
            'Определение тестового сервиса бандла удалилось.'
        );
    }

    /**
     * process().
     *
     * @return void
     */
    public function testProcess(): void
    {
        $this->container->setParameter(
            'custom_arguments_resolvers',
            [
                'params' =>
                [
                    'disabled_resolvers' => [
                        'test.kernel.listener'
                    ]
                ]
            ]
        );

        $this->container->register(
            'test.kernel.listener',
            get_class($this->fakeService)
        );

        $this->testObject->process($this->container);
        $def = $this->container->hasDefinition('test.kernel.listener');

        $this->assertFalse(
            $def,
            'Тестовый сервис не удалился.'
        );
    }

    /**
     * Датапровйдер пустых секций конифга.
     *
     * @return array
     */
    public function dataProviderConfigSectionDisabledService(): array
    {
        return [
            ['params' => null],
            [
                'params' => [
                    'disabled_resolvers' => null,
                ],
            ],
        ];
    }
}