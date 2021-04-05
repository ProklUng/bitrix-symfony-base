<?php

namespace Local\Bundles\StaticPageMakerBundle\Services\Bitrix;

use CIBlockElement;
use RuntimeException;

/**
 * Class SeoMetaElement
 * @package Local\Bundles\StaticPageMakerBundle\Services
 *
 * @since 23.01.2021
 */
class SeoMetaElement
{
    /**
     * @var CIBlockElement $ciblockElement Битриксовый CIBlockElement.
     */
    private $ciblockElement;

    /**
     * @var ElementValuesProxy $elementValuesProxy Прокси к битриксовому ElementValues.
     */
    private $elementValuesProxy;

    /**
     * @var array $arResult
     */
    private $arResult = [];

    /**
     * @var integer $iblockId ID инфоблока.
     */
    private $iblockId;

    /**
     * SeoMetaElement constructor.
     *
     * @param CIBlockElement       $cblockElement     Битриксовый CIBlockElement.
     * @param ElementValuesProxy  $elementValuesProxy Прокси к битриксовому ElementValues.
     * @param integer              $iblockId          ID инфоблока.
     */
    public function __construct(
        CIBlockElement $cblockElement,
        ElementValuesProxy $elementValuesProxy,
        int $iblockId
    ) {
        $this->ciblockElement = $cblockElement;
        $this->iblockId = $iblockId;
        $this->elementValuesProxy = $elementValuesProxy;
    }

    /**
     * Получение общего массива данных.
     *
     * @param string $url
     *
     * @return $this
     */
    public function data(string $url) : self
    {
        $idElement = $this->searchElementByUrl($url);
        if (!$idElement) {
            throw new RuntimeException(
                'Element not found'
            );
        }

        $this->getSeoData($this->iblockId, $idElement);

        return $this;
    }

    /**
     * Title.
     *
     * @return string
     */
    public function title() : string
    {
        return $this->arResult['ELEMENT_META_TITLE']['VALUE'] ?? '';
    }

    /**
     * Description.
     *
     * @return string
     */
    public function description() : string
    {
        return $this->arResult['ELEMENT_META_DESCRIPTION']['VALUE'] ?? '';
    }

    /**
     * @param integer $iblockId  ID инфоблока.
     * @param integer $elementId ID элемента.
     *
     * @return array
     */
    private function getSeoData(int $iblockId, int $elementId)
    {
        $this->elementValuesProxy->setIblockId($iblockId)
                                 ->setElementId($elementId);

        $this->arResult = $this->elementValuesProxy->queryValues();

        return $this->arResult;
    }

    /**
     * Поиск элемента по URL.
     *
     * @param string $url URL статической страницы.
     *
     * @return integer
     */
    private function searchElementByUrl(string $url) : int
    {
        $result = $this->ciblockElement->GetList(
            [],
            [
                'IBLOCK_ID' => $this->iblockId,
                'NAME' => trim($url),
                'ACTIVE' => 'Y'
            ],
            false,
            false,
            ['ID']
        );

        if ($item = $result->Fetch()) {
            return $item['ID'];
        }

        return 0;
    }

}