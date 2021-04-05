<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\Listeners;

use Exception;
use Local\Bundles\CustomArgumentResolverBundle\Event\Listeners\InjectServiceController;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleService;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;


/**
 * Class InjectServiceControllerTest
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Listeners
 * @coversDefaultClass InjectServiceController
 *
 * @since 06.12.2020
 */
class InjectServiceControllerTest extends BaseTestCase
{
    /**
     * @var InjectServiceController $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @var object $class Фэйковый контроллер.
     */
    private $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->obTestObject = new InjectServiceController();
    }

    /**
     * handle(). Контроллер как сервис.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testHandleControllerContainered() : void
    {
        $event = $this->getMockControllerEvent(true);

        $container = new ContainerBuilder();

        $container->register(
            SampleService::class,
            SampleService::class
        );

        $this->obTestObject->setContainer($container);

        $this->obTestObject->handle($event);

        $this->assertSame(
            SampleService::class,
            get_class($event->getController()[0]),
            'Инжекция контроллера из контейнера не сработала.'
        );
    }

    /**
     * handle(). Контроллер как сервис и нет.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testHandleControllerNotContainered() : void
    {
        $event = $this->getMockControllerEvent(true);
        $this->obTestObject->setContainer(static::$testContainer);

        $this->obTestObject->handle($event);

        $this->assertSame(
            get_class($this->class),
            get_class($event->getController()[0]),
            'Инжекция контроллера сработала в неправильном случае.'
        );
    }

    /**
     * handle(). Не MASTER_REQUEST.
     *
     * @return void
     * @throws Exception
     */
    public function testHandleNonMasterRequest() : void
    {
        $event = $this->getMockControllerEvent(false);
        $this->obTestObject->handle($event);

        $this->assertTrue(
            true
        );
    }

    /**
     * Мок ControllerEvent.
     *
     * @param boolean $masterRequest MASTER REQUEST.
     * @param boolean $containered
     *
     * @return mixed
     */
    private function getMockControllerEvent(bool $masterRequest = true, bool $containered = true)
    {
        $controllerResolver = new ControllerResolver();

        $request = $this->getFakeRequest($containered);

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
     * @param bool $containered
     *
     * @return Request
     */
    private function getFakeRequest(bool $containered): Request
    {
        $fakeRequest = Request::create(
            '/api/fake/',
            'GET',
            []
        );

        $this->class = $this->getFakeController($containered);

        $controllerString = get_class($this->class) . '::action';

        $fakeRequest->attributes->set('_controller',
            $controllerString
        );

        $fakeRequest->attributes->set('obj', get_class($this->class));

        return $fakeRequest;
    }

    /**
     * @param bool $containered
     *
     * @return mixed
     */
    private function getFakeController(bool $containered)
    {
        if ($containered) {
            $this->class = new SampleService();
        } else {
            $this->class = new class {
                public function action(Request $request)
                {
                    return new Response('OK');
                }
            };
        }

        return $this->class;
    }
}
