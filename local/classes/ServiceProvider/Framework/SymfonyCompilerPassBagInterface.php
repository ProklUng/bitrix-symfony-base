<?php

namespace Local\ServiceProvider\Framework;

/**
 * Interface SymfonyCompilerPassBagInterface
 * @package Local\ServiceProvider\Framework
 *
 * @since 05.04.2021
 */
interface SymfonyCompilerPassBagInterface
{
    /**
     * @param array $standartCompilerPasses
     *
     * @return void
     */
    public function setStandartCompilerPasses(array $standartCompilerPasses): void;

    /**
     * @return array
     */
    public function getStandartCompilerPasses(): array;
}
