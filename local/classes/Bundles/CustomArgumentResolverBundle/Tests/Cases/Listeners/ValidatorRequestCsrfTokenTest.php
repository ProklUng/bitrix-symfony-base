<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\Listeners;

use Local\Bundles\CustomArgumentResolverBundle\Event\Exceptions\WrongCsrfException;
use Local\Bundles\CustomArgumentResolverBundle\Event\Listeners\ValidatorRequestCsrfToken;
use Local\Bundles\CustomArgumentResolverBundle\Service\Utils\CsrfRequestHandler;
use Local\Bundles\CustomArgumentResolverBundle\Tests\Tools\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class ValidatorRequestCsrfTokenTest
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Listeners
 * @coversDefaultClass ValidatorRequestCsrfToken
 *
 * @since 06.12.2020
 * @since 03.02.2021 Актуализация.
 */
class ValidatorRequestCsrfTokenTest extends BaseTestCase
{
    /**
     * @var ValidatorRequestCsrfToken $obTestObject Тестируемый объект.
     */
    protected $obTestObject;

    /**
     * handle(). Отключенная опцией CSRF защитой.
     *
     * @return void
     *
     * @throws WrongCsrfException
     */
    public function testHandleNoCsrfProtection() : void
    {
        $this->obTestObject = new ValidatorRequestCsrfToken(
            new CsrfRequestHandler(
                static::$testContainer->get(CsrfTokenManagerInterface::class),
                new ParameterBag(['csrf_protection' => false])
            )
        );

        $event = $this->getMockRequestEvent(true, false);
        $this->obTestObject->handle($event);

        $this->assertTrue(
            $event->getRequest()->attributes->get('security.token.validated')
        );
    }

    /**
     * handle(). Процесс с валидным токеном.
     *
     * @return void
     *
     * @throws WrongCsrfException
     */
    public function testHandleValidCsrfToken() : void
    {
        $this->obTestObject = new ValidatorRequestCsrfToken(
            new CsrfRequestHandler(
                static::$testContainer->get(CsrfTokenManagerInterface::class),
                new ParameterBag(['csrf_protection' => true])
            )
        );

        $event = $this->getMockRequestEvent(true, true);
        $this->obTestObject->handle($event);

        $this->assertTrue(
            $event->getRequest()->attributes->get('security.token.validated')
        );
    }

    /**
     * handle(). Процесс с невалидным токеном.
     *
     * @return void
     *
     * @throws WrongCsrfException
     */
    public function testHandleInvalidCsrfToken() : void
    {
        $this->obTestObject = new ValidatorRequestCsrfToken(
            new CsrfRequestHandler(
                static::$testContainer->get(CsrfTokenManagerInterface::class),
                new ParameterBag(['csrf_protection' => true])
            )
        );


        $event = $this->getMockRequestEvent(true, false);

        $this->expectExceptionMessage('Security error: Invalid CSRF token.');
        $this->expectException(WrongCsrfException::class);

        $this->obTestObject->handle($event);
    }

    /**
     * handle(). Не MASTER_REQUEST.
     *
     * @return void
     * @throws WrongCsrfException
     */
    public function testHandleNonMasterRequest() : void
    {
        $this->obTestObject = new ValidatorRequestCsrfToken(
            new CsrfRequestHandler(
                static::$testContainer->get(CsrfTokenManagerInterface::class),
                new ParameterBag(['csrf_protection' => true])
            )
        );

        $event = $this->getMockRequestEvent(false, false);
        $this->obTestObject->handle($event);

        $this->assertFalse(
            $event->getRequest()->attributes->get('security.token.validated')
        );
    }

    /**
     * Мок RequestEvent.
     *
     * @param boolean $masterRequest MASTER_REQUEST.
     * @param boolean $validToken    Использовать валидный токен или нет?
     *
     * @return mixed
     */
    private function getMockRequestEvent(bool $masterRequest = true, bool $validToken = false)
    {
        $request = $this->getFakeRequest($validToken);

        return new RequestEvent(
            static::$testContainer->get('kernel'),
            $request,
            $masterRequest ? HttpKernelInterface::MASTER_REQUEST : HttpKernelInterface::SUB_REQUEST
        );
    }

    /**
     * Создать фэйковый Request.
     *
     * @param boolean $validToken
     *
     * @return Request
     */
    private function getFakeRequest(bool $validToken = false): Request
    {
        $fakeRequest = Request::create(
            '/api/fake/',
            'GET',
            []
        );

        if ($validToken) {
            $fakeRequest->headers->set(
                'x-csrf', static::$testContainer->get('security.csrf.token_manager')->getToken('app')
            );
        } else {
            $fakeRequest->headers->set(
                'x-csrf', $this->faker->slug
            );
        }

        $class = $this->getFakeController();

        $controllerString = get_class($class) . '::action';

        $fakeRequest->attributes->set('_controller',
            $controllerString
        );

        $fakeRequest->attributes->set('obj', get_class($class));

        return $fakeRequest;
    }

    /**
     * @return mixed
     */
    private function getFakeController()
    {
        return new class extends AbstractController {
            public function action(Request $request): Response
            {
                return new Response('OK');
            }
        };
    }
}
