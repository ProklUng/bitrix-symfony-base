<?php

namespace Local\Tests\PhpUnitExtensions;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

class DocCommentParser
{
    /**
     * @param string $className
     * @param callable $mockCreator
     * @throws Exception
     * @return PropertyBag
     */
    public function parse(
        string $className,
        callable $mockCreator
    ): PropertyBag {
        /** @var string[] $res */
        $r = new ReflectionClass($className);
        $docComment = $r->getDocComment();

        if ($docComment === false) {
            return new PropertyBag([], []);
        }

        return $this->parseDocString($docComment, $mockCreator);
    }

    private function parseDocString(
        string $docComment,
        callable $mockCreator
    ): PropertyBag {
        /** @var string[] $map */
        $map = [];
        /** @var string[] $constructorParams */
        $constructorParams = [];

        preg_match_all('/@property\s*(?<mock>(\S*)\|(\S+)\s*\$(\S+))|(?<const>(\S*)\s*\$(\S+))/', $docComment, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {

            if (array_key_exists('mock', $match) && !empty($match['mock'])) {
                if ($this->isMockObject($match[2])) {
                    $map[$match[4]] = $mockCreator($match[3]);
                    $constructorParams[] = $match[4];

                    continue;
                }

                if ($this->isMockObject($match[3])) {
                    $map[$match[4]] = $mockCreator($match[2]);
                    $constructorParams[] = $match[4];

                    continue;
                }
            }

            if (array_key_exists('const', $match) && !empty($match['const'])) {
                if ($this->isConstDependencyInjectionParameter($match[6])) {
                    $map[$match[7]] = new ConstDependencyInjectionParameter();
                    $constructorParams[] = $match[7];
                }
            }
        }

        return new PropertyBag($map, $constructorParams);
    }

    private function isMockObject(string $s): bool
    {
        return $s === MockObject::class
            || $s === '\\' . MockObject::class;
    }

    private function isConstDependencyInjectionParameter($s): bool
    {
        return $s === 'ConstDependencyInjectionParameter'
            || $s === ConstDependencyInjectionParameter::class
            || $s === '\\' . ConstDependencyInjectionParameter::class
            ;
    }
}
