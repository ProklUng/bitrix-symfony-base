<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\Listeners;

use Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions\WrongCsrfException;
use Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions\WrongSecurityTokenException;
use Local\Bundles\CustomArgumentResolverBundle\Event\Listeners\SecurityToken;
use Local\Bundles\CustomArgumentResolverBundle\Event\Traits\ValidatorTraits\SecurityTokenTrait;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * Class SecurityTokenTest
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Listeners
 * @coversDefaultClass SecurityToken
 *
 * @since 06.12.2020
 */
class SecurityTokenTest extends BaseTestCase
{
    /**
     * @var SecurityToken $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->obTestObject = new SecurityToken(
            static::$testContainer->get('custom_arguments_resolvers.security.csrf.token_manager')
        );
    }

    /**
     * handle(). Без передачи токена. Контроллер с трэйтом или нет.
     *
     * @param boolean $traitable Признак - класс с трэйтом, указывающим на необходимость обработки.
     *
     * @return void
     * @throws WrongSecurityTokenException|WrongCsrfException Ошибка проверки токена.
     *
     * @dataProvider dataProviderTrueFalse
     */
    public function testHandle(bool $traitable) : void
    {
        $event = $this->getMockControllerEvent(true, $traitable);

        if ($traitable) {
            $this->expectException(WrongSecurityTokenException::class);
            $this->expectExceptionMessage('Security error: empty token.');
        }

        $this->obTestObject->handle($event);

        if (!$traitable) {
            $this->assertTrue(
                true
            );
        }
    }

    /**
     * handle(). Невалидный токен. Контроллер с трэйтом или нет.
     *
     * @param boolean $traitable Признак - класс с трэйтом, указывающим на необходимость обработки.
     *
     * @return void
     * @throws WrongSecurityTokenException|WrongCsrfException Ошибка проверки токена.
     *
     * @dataProvider dataProviderTrueFalse
     */
    public function testHandleInvalidToken(bool $traitable) : void
    {
        $event = $this->getMockControllerEvent(true, $traitable);
        $event->getRequest()->request->set('security.token', $this->faker->slug);

        if ($traitable) {
            $this->expectException(WrongSecurityTokenException::class);
            $this->expectExceptionMessage('Security error: Invalid security token.');
        }

        $this->obTestObject->handle($event);

        if (!$traitable) {
            $this->assertTrue(
                true
            );
        }
    }

    /**
     * handle(). Невалидный токен. Контроллер с трэйтом или нет.
     *
     * @param boolean $traitable Признак - класс с трэйтом, указывающим на необходимость обработки.
     *
     * @return void
     * @throws WrongSecurityTokenException|WrongCsrfException Ошибка проверки токена.
     *
     * @dataProvider dataProviderTrueFalse
     */
    public function testHandleEmptyToken(bool $traitable) : void
    {
        $event = $this->getMockControllerEvent(true, $traitable);
        $event->getRequest()->request->set('security.token', null);

        if ($traitable) {
            $this->expectException(WrongSecurityTokenException::class);
            $this->expectExceptionMessage('Security error: empty token.');
        }

        $this->obTestObject->handle($event);

        if (!$traitable) {
            $this->assertTrue(
                true
            );
        }
    }

    /**
     * handle(). Валидный токен. Контроллер с трэйтом или нет.
     *
     * @param boolean $traitable Признак - класс с трэйтом, указывающим на необходимость обработки.
     *
     * @return void
     *
     * @throws WrongSecurityTokenException
     * @throws WrongCsrfException
     * @dataProvider dataProviderTrueFalse
     */
    public function testHandleValidToken(bool $traitable) : void
    {
        /**
         * @var CsrfToken $validToken
         */
        $validToken = static::$testContainer->get(
            'custom_arguments_resolvers.security.csrf.token_manager'
        )->getToken('app');

        $event = $this->getMockControllerEvent(true, $traitable);
        $event->getRequest()->request->set('security.token', $validToken->getValue());

        $this->obTestObject->handle($event);

        if ($traitable) {
            $this->assertTrue(
                $event->getRequest()->attributes->get('security.token.validated'),
                'Валидация не прошла (при внешней успешности).'
            );
        }

        // Если что-то не так, то выбросится исключение.
        $this->assertTrue(
            true
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
            'true' => [true],
            'false' => [false],
        ];
    }

    /**
     * handle(). Не MASTER_REQUEST.
     *
     * @return void
     *
     * @throws WrongSecurityTokenException Ошибка проверки токена.
     */
    public function testHandleNonMasterRequest() : void
    {
        $event = $this->getMockControllerEvent(false, false);
        $this->obTestObject->handle($event);

        $this->assertTrue(
            true
        );
    }

    /**
     * Мок ControllerEvent.
     *
     * @param boolean $masterRequest MASTER_REQUEST.
     * @param boolean $traitable     Признак - класс с трэйтом, указывающим на необходимость обработки.
     *
     * @return mixed
     */
    private function getMockControllerEvent(bool $masterRequest = true, bool $traitable = false)
    {
        $controllerResolver = new ControllerResolver();

        $request = $this->getFakeRequest($traitable);

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
     * @param boolean $traitable Признак - класс с трэйтом, указывающим на необходимость обработки.
     *
     * @return Request
     */
    private function getFakeRequest(bool $traitable = false): Request
    {
        $fakeRequest = Request::create(
            '/api/fake/',
            'GET',
            []
        );

        $class = $this->getFakeController($traitable);

        $controllerString = get_class($class) . '::action';

        $fakeRequest->attributes->set(
            '_controller',
            $controllerString
        );

        $fakeRequest->attributes->set('obj', get_class($class));

        return $fakeRequest;
    }

    /**
     * @param boolean $traitable
     *
     * @return mixed
     */
    private function getFakeController(bool $traitable = false)
    {
        $class = new class extends AbstractController {
            public function action(Request $request)
            {
                return new Response('OK');
            }
        };

        if ($traitable) {
            $class = new class {
                use SecurityTokenTrait;

                public function action(Request $request)
                {
                    return new Response('OK');
                }
            };
        }

        return $class;
    }
}
