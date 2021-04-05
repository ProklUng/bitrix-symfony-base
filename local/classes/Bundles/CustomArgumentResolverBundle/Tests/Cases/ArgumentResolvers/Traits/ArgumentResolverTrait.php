<?php

namespace Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Traits;

use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Validator\RequestAnnotationValidatorInterface;
use Mockery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Trait ArgumentResolverTrait
 * @package Local\Bundles\CustomArgumentResolverBundle\Tests\Cases\ArgumentResolvers\Traits
 *
 * @since 03.04.2021
 */
trait ArgumentResolverTrait
{

    /**
     * @param string $paramName Название параметра.
     * @param string $type
     *
     * @return ArgumentMetadata
     */
    private function getMetaArgument(string $paramName, string $type = 'string'): ArgumentMetadata
    {
        return new ArgumentMetadata(
            $paramName,
            $type,
            false,
            false,
            ''
        );
    }

    /**
     * Запрос $_GET.
     *
     * @param string $controller  Класс контроллера.
     * @param array  $getParams   $_GET параметры запроса.
     * @param string $typeRequest Тип запроса.
     * @param string $method      Метод.
     *
     * @return Request
     */
    private function createRequest(
        string $controller,
        array $getParams,
        string $typeRequest = 'GET',
        string $method = 'action'
    ): Request
    {
        $request = new Request(
            $getParams,
            [],
            [
                '_controller' => $controller.'::' . $method,
            ]
        );
        $request->setMethod($typeRequest);

        return $request;
    }

    /**
     * Запрос $_POST.
     *
     * @param string $controller Класс контроллера.
     * @param array  $getParams  $_POST параметры запроса.
     * @param string $method     Метод.
     * @return Request
     */
    private function createRequestPost(string $controller, array $getParams, string $method = 'action'): Request
    {
        $request = new Request(
            [],
            $getParams,
            [
                '_controller' => $controller.'::' . $method,
            ]
        );
        $request->setMethod('POST');

        return $request;
    }

    /**
     * Запрос $_POST.
     *
     * @param string $controller Класс контроллера.
     * @param array  $getParams  $_POST параметры запроса.
     * @param string $method     Метод.
     *
     * @return Request
     */
    private function createRequestJson(string $controller, array $getParams, string $method = 'action'): Request
    {
        $request = new Request(
            [],
            $getParams,
            [
                '_controller' => $controller.'::'.$method,
            ],
            [],
            [],
            [],
            json_encode($getParams)

        );
        $request->setMethod('POST');

        return $request;
    }

    /**
     * Мок RequestAnnotationValidatorInterface.
     *
     * @param boolean $once Вызывается однажды?
     *
     * @return mixed
     */
    private function getMockValidator(bool $once = true)
    {
        $mock = Mockery::mock(RequestAnnotationValidatorInterface::class);
        if ($once) {
            $mock = $mock->shouldReceive('validate')->once();
        } else {
            $mock = $mock->shouldReceive('validate')->never();
        }

        return $mock->getMock();
    }
}