<?php

namespace Local\Bundles\BitrixOgGraphBundle\Services\Facades;

use Local\Bundles\BitrixOgGraphBundle\Services\InjectGraph;
use Local\Bundles\BitrixOgGraphBundle\Services\OgDTO;
use Local\Bundles\BitrixOgGraphBundle\Services\SectionsProcessor;
use Psr\Cache\InvalidArgumentException;

/**
 * Class FacadeOgGraphSection
 * @package Local\Bundles\BitrixOgGraphBundle\Services\Facades
 *
 * @since 19.02.2021
 */
class FacadeOgGraphSection
{
    /**
     * @var SectionsProcessor $sectionProcessor Процессор подразделов.
     */
    private $sectionProcessor;

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
     * @param SectionsProcessor $sectionProcessor Процессор подразделов.
     * @param InjectGraph       $injectGraph      Инжектор.
     * @param OgDTO             $ogDTO            DTO.
     */
    public function __construct(
        SectionsProcessor $sectionProcessor,
        InjectGraph $injectGraph,
        OgDTO $ogDTO
    ) {
        $this->sectionProcessor = $sectionProcessor;
        $this->injector = $injectGraph;
        $this->ogDTO = $ogDTO;
    }

    /**
     * @param integer $iblockId  ID инфоблока.
     * @param integer $idSection ID подраздела.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function make(int $iblockId, int $idSection): void
    {
        $data = $this->sectionProcessor->setIblockId($iblockId)
                                      ->setIdSection($idSection)
                                      ->go();

        $this->ogDTO->update($data);

        $this->injector->inject($this->ogDTO);
    }
}
