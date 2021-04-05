<?php

namespace Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver;

use Doctrine\Common\Annotations\Reader;
use Local\Bundles\CustomArgumentResolverBundle\Annotation\BitrixFileUrl;
use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Services\BitrixFileParam;
use Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver\Traits\ArgumentResolverTrait;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;


/**
 * Class BitrixFileUrlArgumentResolver
 * @package Local\Bundles\CustomArgumentResolverBundle\ArgumentsResolver
 *
 * @description
 *
 * Аннотация метода контроллера - @BitrixFileUrl. Параметры:
 * var - название переменной в action контроллера.
 *
 * @since 01.04.2021
 */
final class BitrixFileUrlArgumentResolver implements ArgumentValueResolverInterface
{
    use ArgumentResolverTrait;

    private const DEFAULT_ANNOTATION = BitrixFileUrl::class;

    /**
     * @var Reader $reader Читатель аннотаций.
     */
    private $reader;

    /**
     * @var ControllerResolver $controllerResolver Controller Resolver.
     */
    private $controllerResolver;

    /**
     * BitrixFileArgumentResolver constructor.
     *
     * @param Reader             $reader             Читатель аннотаций.
     * @param ControllerResolver $controllerResolver Controller Resolver.
     */
    public function __construct(
        Reader $reader,
        ControllerResolver $controllerResolver
    ) {
        $this->reader = $reader;
        $this->controllerResolver = $controllerResolver;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException Ошибки рефлексии.
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $annotation = $this->getAnnotation($request, self::DEFAULT_ANNOTATION);

        if (!$annotation instanceof BitrixFileUrl) {
            return false;
        }

        $var = $annotation->getVar();
        if (!$request->request->has($var) && !$request->query->has($var)) {
            return false;
        }

        return $argument->getName() === $annotation->getVar();
    }

    /**
     * @inheritDoc
     * @throws ReflectionException Ошибки рефлексии.
     * @throws Exceptions\BitrixFileNotFoundException
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $annotation = $this->getAnnotation($request, self::DEFAULT_ANNOTATION);
        $variable = $annotation->getVar();

        $values = $this->getRequestData($request);

        $object = new BitrixFileParam();
        $object->fromId($values[$variable]);

        $request->attributes->set($variable, $object->url());

        yield $object->url();
     }

    /**
     * Данные запроса в зависимости от типа запроса.
     *
     * @param Request $request Request.
     *
     * @return array
     */
    private function getRequestData(Request $request) : array
    {
        // Тип запроса.
        $typeRequest = $request->getMethod();

        return $typeRequest !== 'GET' ?
            $request->request->all()
            :
            $request->query->all();
    }
}
