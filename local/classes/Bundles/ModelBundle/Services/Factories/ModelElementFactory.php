<?php

namespace Local\Bundles\ModelBundle\Services\Factories;

use Arrilot\BitrixModels\Models\ElementModel;
use CIBlockElement;
use LogicException;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class ModelElementFactory
 * @package Local\Bundles\ModelBundle\Services\Factories
 *
 * @since 30.01.2021
 */
class ModelElementFactory
{
    /**
     * @var ServiceLocator $modelLocator Сервисы, помеченные в контейнере тэгом iblock.model.
     */
    private $modelLocator;

    /**
     * @var CIBlockElement $blockElement Битриксовый CIBlockElement.
     */
    private $blockElement;

    /**
     * ModelElementFactory constructor.
     *
     * @param ServiceLocator $locator      Сервисы, помеченные в контейнере тэгом iblock.model.
     * @param CIBlockElement $blockElement Битриксовый CIBlockElement.
     */
    public function __construct(
        ServiceLocator $locator,
        CIBlockElement $blockElement
    ) {
        $this->modelLocator = $locator;
        $this->blockElement = $blockElement;
    }

    /**
     * Модель по ID элемента.
     *
     * @param integer $idElement ID элемента.
     *
     * @return ElementModel
     * @throws LogicException
     */
    public function getModel(int $idElement) : ElementModel
    {
        $iblockId = $this->getIblockId($idElement);

        return $this->getModelByIdIblock($iblockId);
    }

    /**
     * Модель по ID инфоблока.
     *
     * @param integer $iblockId ID инфоблока.
     *
     * @return ElementModel
     * @throws LogicException
     */
    public function getModelByIdIblock(int $iblockId) : ElementModel
    {
        if ($iblockId !== 0) {
            foreach ($this->modelLocator->getProvidedServices() as $serviceId => $value) {
                $service = $this->modelLocator->get($serviceId);
                if ($service::IBLOCK_ID === $iblockId) {
                    return $service;
                }
            }
        }

        throw new LogicException(
            'Model not found.'
        );
    }

    /**
     * ID инфоблока по ID элемента.
     *
     * @param integer $idElement ID элемента.
     *
     * @return integer
     */
    private function getIblockId(int $idElement) : int
    {
        $result = $this->blockElement::GetList(
            [],
            ['ID' => $idElement],
            false,
            false,
            ['IBLOCK_ID']
        );

        if ($data = $result->Fetch()) {
            return (int)$data['IBLOCK_ID'];
        }

        return 0;
    }
}