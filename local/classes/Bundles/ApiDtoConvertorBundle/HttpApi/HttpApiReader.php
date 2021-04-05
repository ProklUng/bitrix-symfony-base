<?php

namespace Local\Bundles\ApiDtoConvertorBundle\HttpApi;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionException;

/**
 * Class HttpApiReader
 * @package Local\Bundles\ApiDtoConvertorBundle\HttpApi
 *
 * @since 04.11.2020
 */
class HttpApiReader
{
    /**
     * @var Reader $reader Читатель аннотаций.
     */
    private $reader;

    /**
     * HttpApiReader constructor.
     *
     * @param Reader $reader Читатель аннотаций.
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Прочитать аннотацию.
     *
     * @param string $className
     * @psalm-param class-string $className
     *
     * @return HttpApi
     *
     * @throws ReflectionException
     */
    public function read(string $className): HttpApi
    {
        /** @var HttpApi|null $annotation */
        $annotation = $this->reader->getClassAnnotation(
            new ReflectionClass($className),
            HttpApi::class
        );

        if ($annotation !== null) {
            return $annotation;
        }

        throw AnnotationNotFoundException::httpApi($className);
    }
}
