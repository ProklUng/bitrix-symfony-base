<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\Listeners;

use Exception;
use Local\Bundles\CustomArgumentResolverBundle\Event\Listeners\BootTraits;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Samples\SampleBootTrait;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class AjaxCallTest
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Listeners
 * @coversDefaultClass BootTraits
 *
 * @since 06.12.2020
 */
class BootTraitsTest extends BaseTestCase
{
    /**
     * @var BootTraits $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obTestObject = new BootTraits();
    }

    /**
     * handle(). Присутствует трэйт с initialize & boot методами.
     *
     * @return void
     * @throws Exception
     */
    public function testHandle() : void
    {
        $class = new class extends AbstractController{

            use SampleBootTrait;

            public function action(Request $request)
            {
                return new Response('OK');
            }
        };

        $this->obTestObject->handle($this->getMockControllerEvent($class));

        $this->assertTrue(
            $class::$init,
            'Инициализация не прошла'
        );

        $this->assertTrue(
            $class::$booted,
            'Загрузка не прошла'
        );
    }

    /**
     * handle(). Класс без трэйта.
     *
     * @return void
     * @throws Exception
     */
    public function testHandleNoTrait() : void
    {
        $class = new class extends AbstractController{
            public static $booted = false;
            public static $init = false;

            public function action(Request $request)
            {
                return new Response('OK');
            }
        };

        $this->obTestObject->handle($this->getMockControllerEvent($class));

        $this->assertFalse(
            $class::$init,
            'Инициализация прошла, а не должна.'
        );

        $this->assertFalse(
            $class::$booted,
            'Инициализация прошла, а не должна.'
        );
    }

    /**
     * Мок ControllerEvent.
     *
     * @param object $class
     *
     * @return mixed
     */
    private function getMockControllerEvent($class)
    {
        $controllerResolver = new ControllerResolver();

        $request = $this->getFakeRequest($class);

        $controller = $controllerResolver->getController($request);

        return new ControllerEvent(
            static::$testContainer->get('kernel'),
            $controller,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    /**
     * Создать фэйковый Request.
     *
     * @param object $class
     *
     * @return Request
     */
    private function getFakeRequest($class): Request
    {
        $fakeRequest = Request::create(
            '/api/fake/',
            'GET',
            []
        );

        $controllerString = get_class($class) . '::action';

        $fakeRequest->attributes->set('_controller',
            $controllerString
        );

        $fakeRequest->attributes->set('obj', get_class($class));


        return $fakeRequest;
    }
}
