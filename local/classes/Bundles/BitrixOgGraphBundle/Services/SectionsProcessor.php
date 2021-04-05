<?php

namespace Local\Bundles\BitrixOgGraphBundle\Services;

use Bitrix\Iblock\InheritedProperty\SectionValues;
use CIBlockSection;
use Local\Bundles\BitrixOgGraphBundle\Services\Utils\CFileWrapper;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class SectionsProcessor
 * @package Local\Bundles\BitrixOgGraphBundle\Services
 *
 * @since 19.02.2021
 */
class SectionsProcessor extends AbstractProcessor
{
    /**
     * @var CIBlockSection $section Битриксовый CIBlockElement.
     */
    private $section;

    /**
     * @var integer $idSection ID подраздела.
     */
    private $idSection;

    /**
     * @var integer $iblockId ID инфоблока.
     */
    private $iblockId;

    /**
     * @var CFileWrapper $fileWrapper Битриксовый CFile.
     */
    private $fileWrapper;

    /**
     * @var CacheInterface $cacher Кэшер.
     */
    private $cacher;

    /**
     * DetailPageProcessor constructor.
     *
     * @param CIBlockSection $section     Битриксовый CIBlockSection.
     * @param CFileWrapper   $fileWrapper Битриксовый CFile.
     * @param CacheInterface $cacher      Кэшер.
     */
    public function __construct(
        CIBlockSection $section,
        CFileWrapper $fileWrapper,
        CacheInterface $cacher
    ) {
        $this->section = $section;
        $this->fileWrapper = $fileWrapper;
        $this->cacher = $cacher;
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    public function go() : array
    {
        $key = md5('og_section' . SITE_ID . $this->iblockId . $this->idSection);

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
     * @param integer $idSection ID элемента.
     *
     * @return self
     */
    public function setIdSection(int $idSection): self
    {
        $this->idSection = $idSection;

        return $this;
    }

    /**
     * Запрос данных на элемент.
     *
     * @return array
     */
    private function query(): array
    {
        $result = $this->section::GetList(
            [],
            [
                'IBLOCK_ID' => $this->iblockId,
                'ID' => $this->idSection,
                'ACTIVE' => 'Y',
            ],
            false,
            [
                'ID',
                'NAME',
                'DESCRIPTION',
                'PICTURE',
                'TIMESTAMP_X',
                'SECTION_PAGE_URL',
            ]
        );

        $arResult = [];

        if ($ob = $result->GetNext()) {
            $ipropValues = new SectionValues($this->iblockId, $this->idSection);
            $values = $ipropValues->queryValues();

            $arResult['title'] = $values['SECTION_META_TITLE']['VALUE'] ?? $ob['NAME'];
            $arResult['description'] = $this->cutDescription(
                $values['SECTION_META_DESCRIPTION']['VALUE'] ?? $ob['DESCRIPTION']
            );
            $arResult['type'] = 'website';
            $arResult['timePublished'] = $ob['TIMESTAMP_X'];
            $arResult['url'] = $this->getFullUrl((string)$ob['SECTION_PAGE_URL']) ?? '';

            $idPicture = (int)$ob['PICTURE'];

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
