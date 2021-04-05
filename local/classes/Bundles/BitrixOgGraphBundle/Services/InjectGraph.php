<?php

namespace Local\Bundles\BitrixOgGraphBundle\Services;

use Bitrix\Main\Page\Asset;

/**
 * Class InjectGraph
 * @package Local\Bundles\BitrixOgGraphBundle\Services
 *
 * @since 19.02.2021
 */
class InjectGraph
{
    /**
     * @var OpenGraphManager $openGraphManager OG менеджер.
     */
    private $openGraphManager;

    /**
     * @var Asset $assetHandler Битриксовый Asset.
     */
    private $assetHandler;

    /**
     * InjectGraph constructor.
     *
     * @param OpenGraphManager $openGraphManager OG менеджер.
     * @param Asset            $assetHandler     Битриксовый Asset.
     */
    public function __construct(
        OpenGraphManager $openGraphManager,
        Asset $assetHandler
    ) {
        $this->openGraphManager = $openGraphManager;
        $this->assetHandler = $assetHandler;
    }

    /**
     * Инжекция в header.php.
     *
     * @param OgDTO $ogDTO Готовое DTO.
     *
     * @return void
     */
    public function inject(
        OgDTO $ogDTO
    ): void {
        $this->openGraphManager->setDto($ogDTO);

        $this->assetHandler->addString($this->openGraphManager->go());
    }
}
