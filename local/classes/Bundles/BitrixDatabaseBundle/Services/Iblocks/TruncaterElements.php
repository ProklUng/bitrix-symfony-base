<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Iblocks;

use CFile;
use CIBlockElement;
use CIBlockResult;

/**
 * Class TruncaterElements
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Iblocks
 *
 * @since 08.04.2021
 */
class TruncaterElements
{
    /**
     * @var CIBlockElement $cblockElement Битриксовый CIBlockElement.
     */
    private $cblockElement;

    /**
     * @var CFile $cfile Битриксовый CFile.
     */
    private $cfile;

    /**
     * TruncaterElements constructor.
     *
     * @param CIBlockElement $cblockElement Битриксовый CIBlockElement.
     * @param CFile          $cfile         Битриксовый CFile.
     */
    public function __construct(CIBlockElement $cblockElement, CFile $cfile)
    {
        $this->cblockElement = $cblockElement;
        $this->cfile = $cfile;
    }

    /**
     * Удаляем все элементы.
     *
     * @param integer $iblockId ID инфоблока.
     *
     * @return integer
     */
    public function deleteElements(int $iblockId): int
    {
        /** @var CIBlockResult $query */
        $query = $this->cblockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId],
            false,
            [],
            ['ID']
        );

        $i = 0;

        while ($result = $query->GetNext()) {
            $id = $result['ID'];
            $this->cblockElement::Delete($id);
            $this->deleteImage($id);
            $i++;
        }

        return $i;
    }

    /**
     * Удалить картинки, приаттаченные к элементу.
     *
     * @param integer $idElement ID элемента.
     *
     * @return void
     */
    private function deleteImage(int $idElement) : void
    {
        $query = $this->cblockElement::GetByID($idElement);

        if ($arRes = $query->GetNext()) {
            $this->cfile::Delete($arRes['DETAIL_PICTURE']);
            $this->cfile::Delete($arRes['PREVIEW_PICTURE']);
        }
    }
}
