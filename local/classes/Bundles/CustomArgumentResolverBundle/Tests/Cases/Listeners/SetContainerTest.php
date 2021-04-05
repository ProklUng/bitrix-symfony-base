<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\Listeners;

use Exception;
use Local\Bundles\CustomArgumentResolverBundle\Event\Listeners\SetContainer;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\PHPUnitUtils;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class AjaxCallTest
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Listeners
 * @coversDefaultClass SetContainer
 *
 * @since 06.12.2020
 */
class SetContainerTest extends BaseTestCase
{
    /**
     * @var SetContainer $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @var object $class Фэйковый контроллер.
     */
    private $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->obTestObject = new SetContainer();
        $this->obTestObject->setContainer(
            static::$testContainer
        );
    }

    /**
     * handle(). Нормальный ход вещей.
     *
     * @param boolean $containerAware
     *
     * @return void
     * @throws ReflectionException
     *
     * @dataProvider dataProviderTrueFalse
     */
    public function testHandle(bool $containerAware) : void
    {
        $event = $this->getMockControllerEvent(true, $containerAware);

        $this->obTestObject->handle($event);
        $controller = $event->getController()[0];

        $result = PHPUnitUtils::getProtectedProperty(
            $controller,
            'container'
        );

        $this->assertNotNull(
            $result,
            'Контейнер не установился.'
        );
    }

    /**
     * Дата-провайдер true-false.
     *
     * @return array
     */
    public function dataProviderTrueFalse() : array
    {
        return [
          [true],
          [false],
        ];
    }

    /**
     * handle(). Не MASTER_REQUEST.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testHandleNonMasterRequest() : void
    {
        $event = $this->getMockControllerEvent(false, false);
        $this->obTestObject->handle($event);

        $result = PHPUnitUtils::getProtectedProperty(
            $this->class,
            'container'
        );

        $this->assertNull(
            $result,
            'Проверка на MASTER_REQUEST не задалась.'
        );
    }

    /**
     * Мок ControllerEvent.
     *
     * @param boolean $masterRequest
     * @param boolean $containerAware
     *
     * @return mixed
     */
    private function getMockControllerEvent(bool $masterRequest = true, bool $containerAware = true)
    {
        $controllerResolver = new ControllerResolver();

        $request = $this->getFakeRequest($containerAware);

        $controller = $controllerResolver->getController($request);

        return new ControllerEvent(
            static::$testContainer->get('kernel'),
            $controller,
            $request,
            $masterRequest ? HttpKernelInterface::MASTER_REQUEST : HttpKernelInterface::SUB_REQUEST
        );
    }

    /**
     * Создать фэйковый Request.
     *
     * @param boolean $containerAware
     *
     * @return Request
     */
    private function getFakeRequest(bool $containerAware = false): Request
    {
        $fakeRequest = Request::create(
            '/api/fake/',
            'GET',
            []
        );

        $this->class = $this->getFakeController($containerAware);

        $controllerString = get_class($this->class) . '::action';

        $fakeRequest->attributes->set('_controller',
            $controllerString
        );

        $fakeRequest->attributes->set('obj', get_class($this->class));

        return $fakeRequest;
    }

    /**
     * @param boolean $containerAware
     *
     * @return mixed
     */
    private function getFakeController(bool $containerAware = false)
    {
        $class = new class extends AbstractController {
            public function action(Request $request)
            {
                return new Response('OK');
            }
        };

        if ($containerAware) {
            $class = new class {
                use ContainerAwareTrait;

                public function action(Request $request)
                {
                    return new Response('OK');
                }
            };
        }

        return $class;
    }
}
