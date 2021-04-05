<?php

namespace Local\Bundles\BitrixOgGraphBundle\Services;

use Bitrix\Iblock\InheritedProperty\ElementValues;
use CIBlockElement;
use Local\Bundles\BitrixOgGraphBundle\Services\Utils\CFileWrapper;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class DetailPageProcessor
 * @package Local\Bundles\BitrixOgGraphBundle\Services
 *
 * @since 19.02.2021
 */
class DetailPageProcessor extends AbstractProcessor
{
    /**
     * @var CIBlockElement $element Битриксовый CIBlockElement.
     */
    private $element;

    /**
     * @var integer $idElement ID элемента.
     */
    private $idElement;

    /**
     * @var integer $iblockId ID инфоблока.
     */
    private $iblockId;

    /**
     * @var CFileWrapper $fileWrapper
     */
    private $fileWrapper;

    /**
     * @var CacheInterface $cacher Кэшер.
     */
    private $cacher;

    /**
     * DetailPageProcessor constructor.
     *
     * @param CIBlockElement $element     Битриксовый CIBlockElement.
     * @param CFileWrapper   $fileWrapper Битриксовый CFile.
     * @param CacheInterface $cacher      Кэшер.
     */
    public function __construct(
        CIBlockElement $element,
        CFileWrapper $fileWrapper,
        CacheInterface $cacher
    ) {
        $this->element = $element;
        $this->fileWrapper = $fileWrapper;
        $this->cacher = $cacher;
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    public function go() : array
    {
        $key = md5('og_element' . SITE_ID . $this->iblockId . $this->idElement);

        return $this->cacher->get($key, function (ItemInterface $item) {
            return $this->query();
        });
    }

    /**
     * @param integer $iblockId ID инфоблока.
     *
     * @return self
     */
    public function setIblockId(int $iblockId): self
    {
        $this->iblockId = $iblockId;

        return $this;
    }

    /**
     * @param integer $idElement ID элемента.
     *
     * @return self
     */
    public function setIdElement(int $idElement): self
    {
        $this->idElement = $idElement;

        return $this;
    }

    /**
     * Запрос данных на элемент.
     *
     * @return array
     */
    private function query(): array
    {
        $result = $this->element::GetList(
            [],
            [
                'IBLOCK_ID' => $this->iblockId,
                'ID' => $this->idElement,
                'ACTIVE' => 'Y',
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'PREVIEW_TEXT',
                'PREVIEW_PICTURE',
                'DETAIL_PICTURE',
                'TIMESTAMP_X',
                'DETAIL_PAGE_URL',
            ]
        );

        $arResult = [];

        if ($ob = $result->GetNext()) {
            $ipropValues = new ElementValues($this->iblockId, $this->idElement);
            $values = $ipropValues->queryValues();

            $arResult['title'] = $values['ELEMENT_META_TITLE']['VALUE'] ?? $ob['NAME'];
            $arResult['description'] = $this->cutDescription(
                $values['ELEMENT_META_DESCRIPTION']['VALUE'] ?? $ob['PREVIEW_TEXT']
            );
            $arResult['type'] = 'article';
            $arResult['timePublished'] = $ob['TIMESTAMP_X'];
            $arResult['url'] = $this->getFullUrl((string)$ob['DETAIL_PAGE_URL']) ?? '';

            $idPicture = (int)$ob['PREVIEW_PICTURE'];
            if (!$idPicture) {
                $idPicture = (int)$ob['DETAIL_PICTURE'];
            }

            if ($idPicture) {
                $resizedPicture = $this->fileWrapper::ResizeImageGet(
                    $idPicture,
                    ['WIDTH' => self::OG_IMAGE_WIDTH, 'HEIGHT' => self::OG_IMAGE_HEIGHT],
                    BX_RESIZE_IMAGE_PROPORTIONAL
                );

                $arResult['img'] = $this->getFullUrl(
                    (string)$resizedPicture['src']
                );
            }
        }

        return $arResult;
    }
 }
