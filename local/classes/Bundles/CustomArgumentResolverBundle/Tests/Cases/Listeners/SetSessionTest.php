<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\Listeners;

use BadMethodCallException;
use Exception;
use Local\Bundles\CustomArgumentResolverBundle\Event\Listeners\SetSession;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class SetSessionTest
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Listeners
 * @coversDefaultClass SetSession
 *
 * @since 10.09.2020
 * @since 28.10.2020 Рефакторинг.
 * @since 05.12.2020 Актуализация.
 * @since 06.12.2020 Рефакторинг.
 */
class SetSessionTest extends BaseTestCase
{
    /**
     * @var SetSession $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obTestObject = new SetSession();
        $this->obTestObject->setContainer(static::$testContainer);
    }

    /**
     * handle(). Нормальный ход вещей.
     *
     * @return void
     * @throws Exception
     */
    public function testHandle() : void
    {
        $event = $this->getMockRequestEvent(true);

        $this->obTestObject->handle($event);
        $result = $event->getRequest()->getSession();

        $this->assertInstanceOf(
            Session::class,
            $result,
            'Установка сессии Symfony не сработала.'
        );
    }

    /**
     * Мок RequestEvent.
     *
     * @param boolean $masterRequest MASTER REQUEST?
     *
     * @return mixed
     */
    private function getMockRequestEvent(bool $masterRequest = false)
    {
        $request = $this->getFakeRequest();

        return new RequestEvent(
            static::$testContainer->get('kernel'),
            $request,
            $masterRequest ? HttpKernelInterface::MASTER_REQUEST : HttpKernelInterface::SUB_REQUEST
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
            '/api/fake/',
            'GET',
            []
        );

        $class = new class extends AbstractController{
            public function action(Request $request)
            {
                return new Response('OK');
            }
        };

        $controllerString = get_class($class) . '::action';

        $fakeRequest->attributes->set('_controller',
            $controllerString
        );

        $fakeRequest->attributes->set('obj', get_class($class));

        return $fakeRequest;
    }
}
