<?php

declare(strict_types=1);

namespace Local\Bundles\DtoMapperBundle\Services;

use AutoMapperPlus\DataType;
use AutoMapperPlus\MappingOperation\Operation;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;
use AutoMapperPlus\AutoMapperInterface;

/**
 * Class Mapper
 * @package Local\Bundles\DtoMapperBundle\Services
 */
class Mapper implements MapperInterface
{
    /**
     * @var AutoMapperInterface $autoMapper MapperPlus.
     */
    private $autoMapper;

    /**
     * @var PropertyInfoExtractorInterface $extractor Экстрактор свойств.
     */
    private $extractor;

    /**
     * @param AutoMapperInterface            $autoMapper MapperPlus.
     * @param PropertyInfoExtractorInterface $extractor  Экстрактор свойств.
     */
    public function __construct(AutoMapperInterface $autoMapper, PropertyInfoExtractorInterface $extractor)
    {
        $this->autoMapper = $autoMapper;
        $this->extractor = $extractor;
    }

    /**
     * @inheritDoc
     */
    public function convert($source, $destination)
    {
        $this->autoConfiguration($source, $destination);
        if (is_object($destination)) {
            return $this->autoMapper->mapToObject($source, $destination);
        }

        return $this->autoMapper->map($source, $destination);
    }

    /**
     * @inheritDoc
     */
    public function convertCollection(iterable $sources, string $destination): iterable
    {
        if (empty($sources)) {
            return [];
        }

        $this->autoConfiguration(end($sources), $destination);

        return $this->autoMapper->mapMultiple($sources, $destination);
    }

    /**
     * @param array|object        $source      Исходник.
     * @param array|object|string $destination Назначение.
     *
     * @return void
     */
    private function autoConfiguration($source, $destination): void
    {
        $destination = is_object($destination) ? get_class($destination) : $destination;
        if (!is_array($source) ||
            $this->autoMapper->getConfiguration()->hasMappingFor('array', $destination)
        ) {
            return;
        }

        $this->createSchemaForMapping($destination);
    }

    /**
     * @param string $destination Назначение.
     */
    private function createSchemaForMapping(string $destination): void
    {
        $config = $this->autoMapper->getConfiguration();
        if (null !== $config->getMappingFor(DataType::ARRAY, $destination)) {
            return;
        }
        $mapping = $config->registerMapping('array', $destination);
        $props = $this->extractor->getProperties($destination);
        foreach ($props as $property) {
            /** @var Type $propertyInfo */
            $types = $this->extractor->getTypes($destination, $property);
            if (!$types) {
                continue;
            }
            $propertyInfo = $types[0];
            $innerClass = false;
            if ($propertyInfo->getCollectionValueType()) {
                $innerClass = $propertyInfo->getCollectionValueType()->getClassName();
                $this->createSchemaForMapping($innerClass);
                $mapping->forMember($property, Operation::mapTo($innerClass));
            } elseif ($propertyInfo->getBuiltinType() === 'object') {
                $innerClass = $propertyInfo->getClassName();
                $this->createSchemaForMapping($innerClass);
                $mapping->forMember($property, Operation::mapTo($innerClass, true));
            }
        }
    }
}
