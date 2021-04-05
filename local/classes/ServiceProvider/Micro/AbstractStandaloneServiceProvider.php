<?php

namespace Local\ServiceProvider\Micro;

use Exception;
use Local\ServiceProvider\Framework\SymfonyCompilerPassBagLight;
use Local\ServiceProvider\ServiceProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class AbstractStandaloneServiceProvider
 * @package Local\ServiceProvider\Micro
 *
 * @since 04.03.2021
 * @since 20.03.2021 Набор стандартных пассов стал protected переменной.
 */
class AbstractStandaloneServiceProvider extends ServiceProvider
{
    /**
     * @var ContainerBuilder $containerBuilder Контейнер.
     */
    protected static $containerBuilder;

    /**
     * @var array $standartCompilerPasses Пассы Symfony.
     */
    protected $standartCompilerPasses;

    /**
     * AbstractStandaloneServiceProvider constructor.
     *
     * @param string $filename Конфиг.
     *
     * @throws Exception Ошибка инициализации контейнера.
     */
    public function __construct(
        string $filename
    ) {
        $this->symfonyCompilerClass = SymfonyCompilerPassBagLight::class;
        parent::__construct($filename);
    }

    /**
     * @inheritDoc
     */
    protected function registerFrameworkExtensions() : void
    {
    }
}
