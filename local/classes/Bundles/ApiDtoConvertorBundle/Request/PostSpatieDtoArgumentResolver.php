<?php

namespace Local\Bundles\ApiDtoConvertorBundle\Request;

use Generator;
use Local\Bundles\ApiDtoConvertorBundle\Errors\ValidateDtoSpatieErrorException;
use Local\Bundles\ApiDtoConvertorBundle\HttpApi\HttpApi;
use Local\Bundles\ApiDtoConvertorBundle\Request\Traits\RequestTrait;
use Local\Bundles\ApiDtoConvertorBundle\Request\Traits\SanitizeTrait;
use Local\Bundles\ApiDtoConvertorBundle\Request\Traits\ValidateTrait;
use Local\Services\Sanitizing\SanitizableTrait;
use Local\Services\Validation\Laravel\LaravelValidatorTrait;
use ReflectionException;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\DataTransferObjectError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Local\Bundles\ApiDtoConvertorBundle\HttpApi\HttpApiReader;
use Local\Bundles\ApiDtoConvertorBundle\HttpApi\AnnotationNotFoundException;

/**
 * Class PostSpatieDtoArgumentResolver
 * С использованием Spatie DTO и валидацией.
 * @package Local\Bundles\ApiDtoConvertorBundle\Request
 *
 * @since 04.11.2020
 */
class PostSpatieDtoArgumentResolver implements ArgumentValueResolverInterface
{
    use LaravelValidatorTrait;
    use ValidateTrait;
    use SanitizableTrait;
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

        if ($className === null
            ||
            !class_exists($className)
            ||
            !class_exists(DataTransferObject::class)
            ||
            !is_subclass_of($className, DataTransferObject::class)
        ) {
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
     * @throws ValidateDtoSpatieErrorException Ошибка создания DTO.
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $className = $argument->getType();

        if ($className === null || !class_exists($className)) {
            throw SupportsException::covered();
        }

        $dto = null;

        // Санация.
        $request = $this->sanitizeDto(
            $request,
            $className
        );

        // Данные запроса.
        $data = $this->getRequestData($request);

        try {
            $dto = new $className($data);
        } catch (DataTransferObjectError $e) {
            throw new ValidateDtoSpatieErrorException(
                $e->getMessage()
            );
        }

        // Валидация.
        $this->validateRequest(
            $request,
            $dto
        );

        yield from [$dto];
    }
}
