<?php

namespace Local\Bundles\BitrixOgGraphBundle\Services\Facades;

use Local\Bundles\BitrixOgGraphBundle\Services\InjectGraph;
use Local\Bundles\BitrixOgGraphBundle\Services\OgDTO;
use Local\Bundles\BitrixOgGraphBundle\Services\StaticPageProcessor;
use Psr\Cache\InvalidArgumentException;

/**
 * Class FacadeOgGraphStatic
 * @package Local\Bundles\BitrixOgGraphBundle\Services\Facades
 *
 * @since 19.02.2021
 */
class FacadeOgGraphStatic
{
    /**
     * @var StaticPageProcessor $staticPageProcessor Процессор статики.
     */
    private $staticPageProcessor;

    /**
     * @var OgDTO $ogDTO DTO.
     */
    private $ogDTO;

    /**
     * @var InjectGraph $injector Инжектор.
     */
    private $injector;

    /**
     * FacadeOgGraphDetailPage constructor.
     *
     * @param StaticPageProcessor $staticProcessor Процессор статики.
     * @param InjectGraph         $injectGraph     Инжектор.
     * @param OgDTO               $ogDTO           DTO.
     */
    public function __construct(
        StaticPageProcessor $staticProcessor,
        InjectGraph $injectGraph,
        OgDTO $ogDTO
    ) {
        $this->staticPageProcessor = $staticProcessor;
        $this->injector = $injectGraph;
        $this->ogDTO = $ogDTO;
    }

    /**
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function make(): void
    {
        $data = $this->staticPageProcessor->go();

        $this->ogDTO->update($data);
        $this->injector->inject($this->ogDTO);
    }
}
