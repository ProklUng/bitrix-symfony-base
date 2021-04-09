<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Iblocks;

use CDBResult;
use CIBlockSection;
use RuntimeException;
use Symfony\Component\String\UnicodeString;

/**
 * Class IblockSections
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Iblocks
 *
 * @since 09.04.2021
 */
class IblockSections
{
    /**
     * @var CIBlockSection $ciblockSection Битриксовый CIBlockSection.
     */
    private $ciblockSection;

    /**
     * IblockSections constructor.
     *
     * @param CIBlockSection $ciblockSection Битриксовый CIBlockSection.
     */
    public function __construct(CIBlockSection $ciblockSection)
    {
        $this->ciblockSection = $ciblockSection;
    }

    /**
     * Добавляет секцию инфоблока.
     *
     * @param integer $iblockId ID инфоблока.
     * @param array   $fields   Поля.
     *
     * @return integer
     * @throws RuntimeException
     */
    public function addSection(int $iblockId, array $fields = []) : int
    {
        $default = [
            'ACTIVE'            => 'Y',
            'IBLOCK_SECTION_ID' => false,
            'NAME'              => 'section',
            'CODE'              => '',
            'SORT'              => 100,
            'PICTURE'           => false,
            'DESCRIPTION'       => '',
            'DESCRIPTION_TYPE'  => 'text',
        ];

        $fields = array_replace_recursive($default, $fields);
        $fields['IBLOCK_ID'] = $iblockId;
        if ($fields['CODE'] === '') {
            $fields['CODE'] = $this->slugify($fields['NAME']);
        }

        $id = $this->ciblockSection->Add($fields);

        if ($id) {
            return (int)$id;
        }

        throw new RuntimeException($this->ciblockSection->LAST_ERROR);
    }

    /**
     * Получает секции инфоблока.
     *
     * @param integer $iblockId ID инфоблока.
     * @param array   $filter   Фильтр.
     *
     * @return array
     */
    public function getSections(int $iblockId, array $filter = []) : array
    {
        $filter['IBLOCK_ID'] = $iblockId;
        $filter['CHECK_PERMISSIONS'] = 'N';

        $dbres = $this->ciblockSection::GetList(
            [
                'SORT' => 'ASC',
            ], $filter, false, [
                'ID',
                'NAME',
                'CODE',
                'IBLOCK_SECTION_ID',
                'SORT',
                'ACTIVE',
                'XML_ID',
                'PICTURE',
                'DESCRIPTION',
                'DESCRIPTION_TYPE',
                'LEFT_MARGIN',
                'RIGHT_MARGIN',
                'DEPTH_LEVEL',
                'DETAIL_PICTURE',
                'UF_*',
            ]
        );

        return $this->fetchAll($dbres);
    }

    /**
     * Обновляет секцию инфоблока.
     *
     * @param integer $sectionId ID подраздела.
     * @param array   $fields    Поля.
     *
     * @throws RuntimeException
     * @return integer
     */
    public function updateSection(int $sectionId, array $fields) : int
    {
        if ($this->ciblockSection->Update($sectionId, $fields)) {
            return $sectionId;
        }

        throw new RuntimeException($this->ciblockSection->LAST_ERROR);
    }

    /**
     * Получает id секции инфоблока
     *
     * @param integer      $iblockId ID инфоблока.
     * @param string|array $code     Код или фильтр.
     *
     * @return integer
     */
    public function getSectionId(int $iblockId, $code) : int
    {
        $item = $this->getSection($iblockId, $code);
        return ($item && isset($item['ID'])) ? $item['ID'] : 0;
    }

    /**
     * Получает секцию инфоблока
     *
     * @param integer      $iblockId ID инфоблока.
     * @param string|array $code     Код или фильтр.
     *
     * @return array
     */
    public function getSection(int $iblockId, $code) : array
    {
        $filter = is_array($code)
            ? $code
            : [
                '=CODE' => $code,
            ];

        $sections = $this->getSections($iblockId, $filter);
        return $sections[0] ?? [];
    }

    /**
     * Удаляет секцию инфоблока
     *
     * @param integer $sectionId ID подраздела.
     *
     * @return boolean
     * @throws RuntimeException
     */
    public function deleteSection(int $sectionId) : bool
    {
        if ($this->ciblockSection::Delete($sectionId)) {
            return true;
        }

        throw new RuntimeException($this->ciblockSection->LAST_ERROR);
    }

    /**
     * @param integer $iblockId ID инфоблока.
     */
    public function deleteAllSections(int $iblockId) : void
    {
        $sections = $this->getSections($iblockId);
        foreach ($sections as $section) {
            $this->deleteSection($section['ID']);
        }
    }

    /**
     * @param CDBResult $dbres
     * @param boolean $indexKey
     * @param boolean $valueKey
     *
     * @return array
     */
    private function fetchAll(CDBResult $dbres, $indexKey = false, $valueKey = false) : array
    {
        $res = [];

        while ($item = $dbres->Fetch()) {
            if ($valueKey) {
                $value = $item[$valueKey];
            } else {
                $value = $item;
            }

            if ($indexKey) {
                $indexVal = $item[$indexKey];
                $res[$indexVal] = $value;
            } else {
                $res[] = $value;
            }
        }

        return $res;
    }

    /**
     * @param string $text
     * @return string
     *
     * @see https://stackoverflow.com/questions/2955251/php-function-to-make-slug-url-string
     */
    public function slugify(string $text) : string
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
