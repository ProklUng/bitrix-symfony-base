<?php

declare(strict_types=1);

namespace Local\Bundles\ApiDtoConvertorBundle\Response;

use Local\Bundles\ApiDtoConvertorBundle\HttpApi\AnnotationNotFoundException;
use Local\Bundles\ApiDtoConvertorBundle\HttpApi\HttpApiReader;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Spatie\DataTransferObject\DataTransferObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ResponseListener
 * @package Local\Bundles\ApiDtoConvertorBundle\Response
 *
 * @since 05.11.2020
 */
class ResponseListener
{
    /**
     * @var HttpApiReader $httpApiReader Читатель аннотаций.
     */
    private $httpApiReader;
    /**
     * @var SerializerInterface $serializer Сериалайзер.
     */
    private $serializer;

    /**
     * ResponseListener constructor.
     *
     * @param HttpApiReader       $httpApiReader Читатель аннотаций.
     * @param SerializerInterface $serializer    Сериалайзер.
     */
    public function __construct(
        HttpApiReader $httpApiReader,
        SerializerInterface $serializer
    ) {
        $this->httpApiReader = $httpApiReader;
        $this->serializer = $serializer;
    }

    /**
     * Обработчик события.
     *
     * @param ViewEvent $viewEvent Объект события.
     *
     * @throws ReflectionException
     */
    public function transform(ViewEvent $viewEvent): void
    {
        /** @var object[]|object|array $controllerResult */
        $controllerResult = $viewEvent->getControllerResult();

        if ($controllerResult !== [] && !$this->hasHttpApi($controllerResult)) {
            return;
        }

        $viewEvent->setResponse(
            $this->createResponse($controllerResult)
        );
    }

    /**
     * Подлежит обработке?
     *
     * @param $controllerResult
     *
     * @return boolean
     * @throws ReflectionException
     */
    private function hasHttpApi($controllerResult): bool
    {
        $object = $controllerResult;

        if (is_array($controllerResult)) {
            /** @var object|mixed $object */
            $object = current($controllerResult);
        }

        if (!is_object($object)) {
            return false;
        }

        try {
            $this->httpApiReader->read(get_class($object));
        } catch (AnnotationNotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * Создать Response.
     *
     * @param object|array $data DTO. Или массив DTO.
     *
     * @return Response
     * @throws ReflectionException
     */
    private function createResponse($data): Response
    {
        $array = [];

        // SpatieDTO.
        if ($data instanceof DataTransferObject) {
            $array = $data->toArray();
        } elseif (is_array($data)) {
            // Массив простых DTO.
            foreach ($data as $item) {
                $array[] = $this->all($item);
            }
        } else {
            $array = $this->all($data);
        }

        $array = array_merge(['error' => false], $array);

        return new Response(
            $this->serializer->serialize($array, 'json'),
            Response::HTTP_OK,
            ['Content-Type',  'application/json; charset=utf-8']
        );
    }

    /**
     * Публичные свойства DTO.
     *
     * @param $dto
     *
     * @return array
     *
     * @throws ReflectionException
     */
    private function all($dto): array
    {
        $data = [];

        $classname = get_class($dto);
        $class = new ReflectionClass($classname);

        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $reflectionProperty) {
            // Skip static properties
            if ($reflectionProperty->isStatic()) {
                continue;
            }

            $data[$reflectionProperty->getName()] = $reflectionProperty->getValue($dto);
        }

        return $data;
    }
}
