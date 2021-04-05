<?php

namespace Local\Bundles\ApiDtoConvertorBundle\Request;

use Generator;
use Local\Bundles\ApiDtoConvertorBundle\HttpApi\HttpApi;
use Local\Bundles\ApiDtoConvertorBundle\Request\Traits\RequestTrait;
use Local\Bundles\ApiDtoConvertorBundle\Request\Traits\SanitizeTrait;
use Local\Bundles\ApiDtoConvertorBundle\Request\Traits\ValidateTrait;
use Local\Services\Validation\Laravel\LaravelValidatorTrait;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Local\Bundles\ApiDtoConvertorBundle\HttpApi\HttpApiReader;
use Local\Bundles\ApiDtoConvertorBundle\HttpApi\AnnotationNotFoundException;

/**
 * Class PostDtoArgumentResolver
 * С использованием любого DTO, помеченного аннотацией.
 * @package Local\Bundles\ApiDtoConvertorBundle\Request
 *
 * @since 04.11.2020
 */
class PostDtoArgumentResolver implements ArgumentValueResolverInterface
{
    use LaravelValidatorTrait;
    use ValidateTrait;
    use SanitizeTrait;
    use RequestTrait;

    /**
     * @var HttpApiReader $httpApiReader Читатель аннотаций.
     */
    private $httpApiReader;

    /**
     * AttributesDtoArgumentResolver constructor.
     *
     * @param HttpApiReader $httpApiReader Читатель аннотаций.
     */
    public function __construct(
        HttpApiReader $httpApiReader
    ) {
        $this->httpApiReader = $httpApiReader;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $className = $argument->getType();

        if ($className === null || !class_exists($className)) {
            return false;
        }

        try {
            $httpApi = $this->httpApiReader->read($className);
        } catch (AnnotationNotFoundException | ReflectionException $e) {
            return false;
        }

        return $httpApi->requestInfoSource === HttpApi::POST;
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return Generator
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $className = $argument->getType();

        if ($className === null || !class_exists($className)) {
            throw SupportsException::covered();
        }

        // Санация.
        $request = $this->sanitizeDto(
            $request,
            $className
        );

        $dto = new $className;

        // Валидация.
        $this->validateRequest(
            $request,
            $dto
        );

        // Данные запроса.
        $data = $this->getRequestData($request);

        foreach ($data as $name => $value) {
            if (property_exists($dto, $name)) {
                $dto->{$name} = $value;
            }
        }

        yield from [$dto];
    }
}
