<?php

namespace Local\ServiceProvider\Framework;

/**
 * Class AbstractSymfonyCompilerPassBag
 * @package Local\ServiceProvider\Framework
 *
 * @since 05.04.2021
 */
class AbstractSymfonyCompilerPassBag implements SymfonyCompilerPassBagInterface
{
    /**
     * @var array $standartCompilerPasses Пассы Symfony.
     */
    protected $standartCompilerPasses = [];

    /**
     * @param array $standartCompilerPasses Compile passes.
     *
     * @return void
     */
    public function setStandartCompilerPasses(array $standartCompilerPasses) : void
    {
        $this->standartCompilerPasses = $standartCompilerPasses;
    }

    /**
     * @return array
     */
    public function getStandartCompilerPasses(): array
    {
        return $this->standartCompilerPasses;
    }
}
