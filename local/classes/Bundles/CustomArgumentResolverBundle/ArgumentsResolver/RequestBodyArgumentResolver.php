<?php

namespace Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver;

use Doctrine\Common\Annotations\Reader;
use Local\Bundles\CustomArgumentResolverBundle\Annotation\RequestBody;
use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Contracts\UnserializableRequestInterface;
use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Exceptions\ValidateErrorException;
use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Traits\ArgumentResolverTrait;
use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Validator\RequestAnnotationValidatorInterface;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class RequestBodyArgumentResolver
 * @package Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver
 *
 * @description
 *
 * Аннотация метода контроллера - @RequestBody. Параметры (не обязательные)
 * var - название переменной в action контроллера,
 * class - класс переменной в action контроллера.
 * Если не указано ни того, ни другого, то ресолвер проверяет - не реализует ли класс
 * интерфейс UnserializableRequestInterface. Если да, то этот аргумент - наш клиент.
 * Рефлексией берет название переменной.
 * Параметр validate = true/false. Валидировать через аннотации. По умолчанию - да.
 *
 * @since 01.04.2021
 */
final class RequestBodyArgumentResolver implements ArgumentValueResolverInterface
{
    use ArgumentResolverTrait;

    private const DEFAULT_ANNOTATION = RequestBody::class;

    /**
     * @var Reader $reader Читатель аннотаций.
     */
    private $reader;

    /**
     * @var ControllerResolver $controllerResolver Controller Resolver.
     */
    private $controllerResolver;

    /**
     * @var SerializerInterface $serializer Сериалайзер.
     */
    private $serializer;

    /**
     * @var RequestAnnotationValidatorInterface $validator
     */
    private $validator;

    /**
     * RequestBodyArgumentResolver constructor.
     *
     * @param Reader                              $reader             Читатель аннотаций.
     * @param ControllerResolver                  $controllerResolver Controller Resolver.
     * @param SerializerInterface                 $serializer         Сериалайзер.
     * @param RequestAnnotationValidatorInterface $validator          Валидатор.
     */
    public function __construct(
        Reader $reader,
        ControllerResolver $controllerResolver,
        SerializerInterface $serializer,
        RequestAnnotationValidatorInterface $validator
    ) {
        $this->reader = $reader;
        $this->controllerResolver = $controllerResolver;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException Ошибки рефлексии.
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        // Если не json (или не подходящий тип запроса), то не работаем.
        if (!$this->isValidRequestType($request) || !$this->isJson($request->getContent())) {
            return false;
        }

        $annotation = $this->getAnnotation($request, self::DEFAULT_ANNOTATION);

        if (!$annotation instanceof RequestBody) {
            return false;
        }

        $variable = $annotation->getVar() ?: $argument->getName();
        if ($argument->getName() !== $variable) {
            return false;
        }

        if (!class_exists($argument->getType())) {
            $this->throwMismatchException($argument->getName(), $argument->getType());
        }

        // Проверка на интерфейс, если не задан класс напрямую в аннотации.
        $interfaces = class_implements($argument->getType());
        if (!$annotation->getClass()
            && !in_array(UnserializableRequestInterface::class, $interfaces, true)
        ) {
            $this->throwMismatchImplementedInterface($argument->getName());
        }

        return true;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException    Ошибки рефлексии.
     * @throws ValidateErrorException Ошибки валидации.
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $annotation = $this->getAnnotation($request, self::DEFAULT_ANNOTATION);
        $class = $annotation->getClass() ?: $argument->getType();

        // Если класс наследует Spatie DTO, то инстанцируем его иным образом.
        if ($this->isSpatieDto($class)) {
            $values = json_decode($request->getContent(), true);
            $object = new $class($values);
        } else {
            $object = $this->serializer->deserialize($request->getContent(), $class, 'json', []);
        }

        if ($annotation->isValidate()) {
            $this->validator->validate($object, $class);
        }

        $variable = $annotation->getVar() ?: $argument->getName();
        $request->attributes->set($variable, $object);

        yield $object;
    }

    /**
     * Подходящий тип запроса?
     *
     * @param Request $request Request.
     *
     * @return boolean
     */
    private function isValidRequestType(Request $request) : bool
    {
        $method = $request->getMethod();

        if ($method === 'GET' || $method === 'HEAD') {
            return false;
        }

        return true;
    }
}