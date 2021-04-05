<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\Listeners;

use Exception;
use Local\Bundles\CustomArgumentResolverBundle\Service\Utils\ResolveParamsFromContainer;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ResolveParamsFromContainerTest
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Listeners
 * @coversDefaultClass ResolveParamsFromContainer
 *
 * @since 06.12.2020
 */
class ResolveParamsFromContainerTest extends BaseTestCase
{
    /**
     * @var ResolveParamsFromContainer $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @var ContainerBuilder $container
     */
    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new ContainerBuilder();
        $this->obTestObject = new ResolveParamsFromContainer();
    }

    /**
     * resolve(). Пустые параметры или объект.
     *
     * @param $value
     *
     * @return void
     *
     * @dataProvider getProviderInvalidParams
     */
    public function testResolveIncorrectEntryParams($value): void
    {
        $result = $this->obTestObject->resolve($value);

        $this->assertSame(
            $value,
            $result
        );
    }

    /**
     * Некорректные входные параметры.
     *
     * @return array
     */
    public function getProviderInvalidParams(): array
    {
        return [
            [
                [],
            ],
            [
                [
                    '',
                ],
            ],
            [
                [
                    null,
                ],
            ],
            [
                null
            ],
            [
                ''
            ],
            [
                0
            ],
            [
                new class {}
            ],
        ];
    }

    /**
     * resolve(). Переменная из контейнера.
     *
     * @return void
     * @throws Exception
     */
    public function testResolveContainerVariable(): void
    {
        $value = $this->faker->sentence();
        $this->container->setParameter('test_param', $value);

        $this->obTestObject->setContainer(
            $this->container
        );

        $result = $this->obTestObject->resolve('%test_param%');

        $this->assertSame(
            $value,
            $result,
            'Переменная из контейнера не разрешилась.'
        );
    }

    /**
     * resolve(). Несуществующая переменная из контейнера.
     *
     * @return void
     * @throws Exception
     */
    public function testResolveContainerInvalidVariable(): void
    {
        $variable = $this->faker->sentence();

        $this->obTestObject->setContainer(
            $this->container
        );

        $result = $this->obTestObject->resolve("%$variable%");

        $this->assertNull(
            $result,
            'Несуществующая переменная из контейнера почему-то разрешилась.'
        );
    }

    /**
     * resolve(). Валидный сервис из контейнера.
     *
     * @return void
     * @throws Exception
     */
    public function testResolveContainerValidService(): void
    {
        $service = new class {};
        $idService = 'test.service';

        $this->container->register(
            $idService,
            get_class($service)
        );

        $this->obTestObject->setContainer(
            $this->container
        );

        $result = $this->obTestObject->resolve("@$idService");

        $this->assertSame(
            get_class($service),
            get_class($result),
            'Существующий сервер на разрешился.'
        );
    }

    /**
     * resolve(). Несуществующий сервис из контейнера.
     *
     * @return void
     * @throws Exception
     */
    public function testResolveContainerNonValidService(): void
    {
        $idFakeService = $this->faker->slug();

        $this->obTestObject->setContainer(
            $this->container
        );

        $this->willSeeException(ServiceNotFoundException::class);

        $this->obTestObject->resolve("@$idFakeService");
    }

    /**
     * resolve(). Алиас сервиса в переменной.
     *
     * @return void
     * @throws Exception
     */
    public function testResolveAliasServiceInVariable(): void
    {
        $service = new class {};
        $idService = 'test.service';

        $this->container->register(
            $idService,
            get_class($service)
        );

        $this->container->setParameter($idService, $idService);

        $this->obTestObject->setContainer(
            $this->container
        );

        $result = $this->obTestObject->resolve("%$idService%");

        $this->assertSame(
            get_class($service),
            get_class($result),
            'Существующий сервер на разрешился.'
        );
    }
}
