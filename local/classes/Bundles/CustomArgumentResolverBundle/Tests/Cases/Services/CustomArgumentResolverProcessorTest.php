<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\Services;

use Exception;
use Local\Bundles\CustomArgumentResolverBundle\Event\InjectorController\CustomArgumentResolverProcessor;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleController;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleControllerArguments;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleControllerDependency;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;

/**
 * Class CommonProcessor
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\Services
 * @coversDefaultClass CustomArgumentResolverProcessor
 *
 * @since 05.12.2020 Актуализация.
 */
class CustomArgumentResolverProcessorTest extends BaseTestCase
{
    /**
     * @var CustomArgumentResolverProcessor $testObject Тестируемый объект.
     */
    protected $testObject;

    /** @var string $controllerClass Класс контроллера для теста. */
    private $controllerClass = SampleController::class;

    private $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = static::$testContainer;

        $this->testObject = new CustomArgumentResolverProcessor(
            $this->container->get('custom_arguments_resolvers.container.aware.resolver'),
            $this->container->get('custom_arguments_resolvers.ignored.autowiring.controller.arguments'),
        );

        $this->testObject->setContainer($this->container);
    }

    /**
     * inject().
     *
     * @return void
     * @throws Exception
     */
    public function testInject(): void
    {
        /** @var ControllerEvent $mockEvent */
        $mockEvent = $this->getMockControllerEvent();

        $result = $this->testObject->inject(
            $mockEvent
        );

        $resultInjection = $result->getRequest()->attributes->get('obj');

        $this->assertInstanceOf(
            SampleControllerDependency::class,
            $resultInjection,
            'Инжекция не прошла.'
        );
    }

    /**
     * Разрешение переменных из контейнера.
     */
    public function testResolveVariableFromContainer(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleControllerArguments::action',
                'obj' => SampleControllerArguments::class,
                'value' => '%my.instagram.token%',
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $result = $this->testObject->inject(
            $event
        );

        $attributes = $result->getRequest()->attributes->all();

        $this->assertNotEmpty(
            $attributes
        );
    }

    /**
     * Разрешение Session.
     *
     * @return void
     * @throws Exception
     */
    public function testSessionResolve(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleController::action2',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertInstanceOf(
            SessionInterface::class,
            $event->getRequest()->attributes->get('session')
        );
    }

    /**
     * Разрешение Defaults value.
     */
    public function testDefaultsResolve(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleController::action3',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            'OK',
            $event->getRequest()->attributes->get('value')
        );
    }

    /**
     * Разрешение Defaults value. Не портит ли значение по умолчанию передаваемое значение.
     */
    public function testWithoutDefaults(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleController::action3',
                'obj' => SampleController::class,
            ]
        );

        $request->attributes->add(['value' => 'OK3']);

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            'OK3',
            $event->getRequest()->attributes->get('value')
        );
    }

    /**
     * Разрешение Defaults value. Constants
     */
    public function testDefaultsResolveConstants(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleController::action4',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            'OK3',
            $event->getRequest()->attributes->get('value')
        );
    }

    /**
     * Разрешение Defaults value. Array
     */
    public function testDefaultsResolveArray(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleController::action4',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            [1, 2, 3],
            $event->getRequest()->attributes->get('array')
        );
    }

    /**
     * Разрешение Defaults value. Array recursively.
     *
     * @return void
     * @throws Exception
     */
    public function testDefaultsResolveArrayRecursive(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleController::action5',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            [
                1,
                2,
                [$this->container->getParameter('kernel.cache_dir')],
                [$this->container->get('session.instance')],
            ],
            $event->getRequest()->attributes->get('array')
        );
    }

    /**
     * Разрешение Invalid service alias.
     */
    public function testDefaultsResolveInvalidServiceAlias(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleController::action6',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->willSeeException(ServiceNotFoundException::class);
        $this->testObject->inject(
            $event
        );
    }

    /**
     * Разрешение Invalid variable.
     *
     * @return void
     * @throws Exception
     */
    public function testDefaultsResolveInvalidVariable(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleController::action7',
                'obj' => SampleController::class,
            ]
        );

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            '%invalid.variable%',
            $event->getRequest()->attributes->get('value')
        );
    }

    /**
     * Инжекция не объектов, а переменных.
     *
     * @return void
     * @throws Exception
     */
    public function testInjectNonObjects(): void
    {
        $request = new Request(
            [],
            [],
            [
                '_controller' => '\Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleController::action8',
                'obj' => SampleController::class,
            ]
        );

        $request->attributes->add(['value' => 'OK3', 'id' => 1]);

        $event = $this->getMockControllerEvent($request);

        $this->testObject->inject(
            $event
        );

        $this->assertSame(
            'OK3',
            $event->getRequest()->attributes->get('value')
        );

        $this->assertSame(
            1,
            $event->getRequest()->attributes->get('id')
        );

    }

    /**
     * @return void
     * @throws Exception
     *
     */
    public function testArgumentResolver(): void
    {
        /**
         * @var ArgumentResolver $app
         */
        $app = container()->get('argument_resolver');

        $request = new Request(
            [],
            [],
            [
                '_controller' => 'Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleController::action8',
                'obj' => SampleController::class,
            ]
        );

        $request->attributes->add(['value' => 'OK3', 'id' => 1, 'array' => []]);

        $app->getArguments($request, [new SampleController(), 'action5']);

        $this->assertSame(
            [1, 2, [$_SERVER['HTTP_HOST']], [$this->container->get('session.instance')]],
            $request->attributes->get('array')
        );
    }

    /**
     * Мок ControllerEvent.
     *
     * @param Request $request
     *
     * @return mixed|ControllerEvent
     * @throws Exception
     */
    private function getMockControllerEvent(Request $request = null)
    {
        $controllerResolver = new ControllerResolver();
        if ($request === null) {
            $request = $this->getFakeRequest();
        }

        $controller = $controllerResolver->getController($request);

        return new ControllerEvent(
            $this->container->get('kernel'),
            $controller,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    /**
     * Создать фэйковый Request.
     *
     * @return Request
     */
    private function getFakeRequest(): Request
    {
        $fakeRequest = Request::create(
            '/api/elastic/search/',
            'GET',
            []
        );

        $fakeRequest->attributes->set('_controller', $this->controllerClass.'::action');
        $fakeRequest->attributes->set('obj', SampleControllerDependency::class);
        $fakeRequest->headers = new HeaderBag(['x-requested-with' => 'XMLHttpRequest']);

        return $fakeRequest;
    }
}
