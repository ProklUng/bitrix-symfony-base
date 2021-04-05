<?php

namespace Local\Bundles\StaticPageMakerBundle\Services\Bitrix;

use Bitrix\Iblock\InheritedProperty\ElementValues;

/**
 * Class ElementValuesProxy
 * @package Local\Bundles\StaticPageMakerBundle\Services\Bitrix
 *
 * @since 23.01.2021
 */
class ElementValuesProxy
{
    /**
     * @var integer $iblockId ID инфоблока.
     */
    private $iblockId;

    /**
     * @var integer $elementId ID элемента.
     */
    private $elementId;

    /**
     * @return array
     */
    public function queryValues() : array
    {
        $ipropValues = new ElementValues($this->iblockId, $this->elementId);

        return $ipropValues->queryValues();
    }

    /**
     * @param integer $iblockId
     *
     * @return $this
     */
    public function setIblockId(int $iblockId): self
    {
        $this->iblockId = $iblockId;

        return $this;
    }

    /**
     * @param integer $elementId
     * @return $this
     */
    public function setElementId(int $elementId): self
    {
        $this->elementId = $elementId;

        return $this;
    }
}