<?php

namespace Prokl\Ckeditor;

use Bitrix\Main\Application;
use Bitrix\Main\Db\SqlQueryException;
use CFile;
use CMedialib;
use CMedialibCollection;
use CModule;
use COption;

/**
 * Class Medialib
 *
 * @package Prokl\Ckeditor
 */
class MediaLib
{
    /**
     * @var integer $once
     */
    private static $once = 0;

    /**
     * @return void
     */
    private static function initialize() : void
    {
        if (!self::$once) {
            CModule::IncludeModule('fileman');
            CMedialib::Init();
            self::$once = 1;
        }
    }

    /**
     * @param array $filter Фильтр.
     *
     * @return array
     */
    public static function getCollections($filter = []) : array
    {
        self::initialize();
        $mltypes = CMedialib::GetTypes();

        $filter['type'] = isset($filter['type']) ? $filter['type'] : 'image';

        $typeid = 0;
        foreach ($mltypes as $mltype) {
            if ($mltype['code'] == $filter['type']) {
                $typeid = $mltype['id'];
                break;
            }
        }

        if ($typeid > 0) {
            return CMedialibCollection::GetList(
                [
                    'arFilter' => [
                        'TYPES' => [$typeid],
                    ],
                ]
            );
        } else {
            return [];
        }
    }

    /**
     * @param array $filter
     * @param array $navParams
     * @param array $resizePreview
     * @param array $resizeDetail
     *
     * @return array
     * @throws SqlQueryException
     */
    public static function getElements(
        array $filter,
        array $navParams = [],
        array $resizePreview = [],
        array $resizeDetail = []
    ) : array {
        self::initialize();

        $connection = Application::getConnection();

        $arResult = [
            'items'      => [],
            'page_count' => 1,
            'page_num'   => 1,
        ];

        $whereQuery = [];
        if (!empty($filter['collection_id'])) {
            if (is_array($filter['collection_id'])) {
                $filter['collection_id'] = array_map(
                    function ($val) {
                        return intval($val);
                    }, $filter['collection_id']
                );

                $whereQuery[] = 'MCI.COLLECTION_ID in (' . implode(',', $filter['collection_id']) . ')';
            } elseif (intval($filter['collection_id']) > 0) {
                $whereQuery[] = 'MCI.COLLECTION_ID=' . intval($filter['collection_id']);
            }
        }

        if (!empty($filter['id'])) {
            if (is_array($filter['id'])) {
                $filter['id'] = array_map(
                    function ($val) {
                        return intval($val);
                    }, $filter['id']
                );

                $whereQuery[] = 'MI.ID in (' . implode(',', $filter['id']) . ')';
            } elseif (intval($filter['id']) > 0) {
                $whereQuery[] = 'MI.ID=' . intval($filter['id']);
            }
        }

        if (empty($whereQuery)) {
            $whereQuery[] = '1=1';
        }

        $limitQuery = '';
        $whereQuery = implode(' AND ', $whereQuery);

        if (isset($navParams['page_size'])) {
            $queryText = "SELECT COUNT(*) cnt
                FROM 
                    b_medialib_collection_item MCI
                INNER JOIN 
                    b_medialib_item MI ON (MI.ID=MCI.ITEM_ID)
                INNER JOIN 
                    b_file F ON (F.ID=MI.SOURCE_ID) 
                WHERE " . $whereQuery . ";";

            $allcount = $connection->query($queryText)->fetch();
            $allcount = ($allcount && $allcount['cnt']) ? $allcount['cnt'] : 0;

            $pagesize = intval($navParams['page_size']);
            $pagesize = $pagesize >= 1 ? $pagesize : 10;

            $pagenum = intval($navParams['page_num']);
            $pagenum = ($pagenum >= 1) ? $pagenum : 1;

            $arResult['page_count'] = ceil($allcount / $pagesize);
            $arResult['page_num'] = $pagenum;

            $navoffsset = ($pagenum - 1) * $pagesize;
            $limitQuery = 'LIMIT ' . $navoffsset . ',' . $pagesize;
        }

        $resizePreview = array_merge(
            [
                'width'  => COption::GetOptionInt('fileman', "ml_thumb_width", 140),
                'height' => COption::GetOptionInt('fileman', "ml_thumb_height", 105),
                'exact'  => 0,
            ],
            $resizePreview
        );

        $queryText = "SELECT MI.*,MCI.COLLECTION_ID, F.HEIGHT, F.WIDTH, F.FILE_SIZE, F.CONTENT_TYPE, F.SUBDIR, F.FILE_NAME, F.HANDLER_ID
            FROM 
                b_medialib_collection_item MCI
            INNER JOIN 
                b_medialib_item MI ON (MI.ID=MCI.ITEM_ID)
            INNER JOIN 
                b_file F ON (F.ID=MI.SOURCE_ID) 
            WHERE " . $whereQuery . " " . $limitQuery . ";";

        $dbres = $connection->query($queryText);

        while ($aImage = $dbres->fetch()) {
            $aItem = self::resizeImage($aImage, $resizePreview);
            $aItem['DETAIL_SRC'] = $aItem['SRC'];

            if (!empty($resizeDetail)) {
                $aDetail = self::resizeImage($aImage, $resizeDetail);
                $aItem['DETAIL_SRC'] = $aDetail['SRC'];
            }

            $arResult['items'][] = $aItem;
        }

        return $arResult;
    }

    /**
     * @param mixed $image        Картинка (ID или массив).
     * @param array $resizeParams Параметры.
     *
     * @return array|false|mixed
     */
    public static function resizeImage($image, array $resizeParams = [])
    {
        $resizeParams = array_merge(
            [
                'width'       => 0,
                'height'      => 0,
                'exact'       => 0,
                'init_sizes'  => false,
                'filters'     => false,
                'immediate'   => false,
                'jpg_quality' => false,
            ],
            $resizeParams
        );

        if (is_numeric($image)) {
            $image = CFile::GetFileArray($image);
        }

        $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;
        if ($resizeParams['exact']) {
            $resizeType = BX_RESIZE_IMAGE_EXACT;
        }

        if ($image && empty($image['SRC'])) {
            $image['SRC'] = CFile::GetFileSRC($image);
        }

        if ($resizeParams['width'] > 0 && $resizeParams['height'] > 0) {
            $size = [
                'width'  => $resizeParams['width'],
                'height' => $resizeParams['height'],
            ];

            $resized = CFile::ResizeImageGet(
                $image,
                $size,
                $resizeType,
                $resizeParams['init_sizes'],
                $resizeParams['filters'],
                $resizeParams['immediate'],
                $resizeParams['jpg_quality']
            );

            $image = [
                'ID'            => $image['ID'],
                'COLLECTION_ID' => $image['COLLECTION_ID'],
                'WIDTH'         => $resized['width'],
                'HEIGHT'        => $resized['height'],
                'SRC'           => self::urlencodePath($resized['src']),
                'ORIGIN_SRC'    => self::urlencodePath($image['SRC']),
                'NAME'          => $image['NAME'],
                'DESCRIPTION'   => htmlspecialcharsbx($image['DESCRIPTION']),
            ];
        }

        return $image;
    }

    /**
     * @param string $path Путь.
     *
     * @return string
     */
    protected static function urlencodePath(string $path) : string
    {
        $url = parse_url($path);
        $path = str_replace("\\", "/", $url["path"]);
        $parts = explode("/", $path);
        $partsEncoded = [];

        foreach ($parts as $part) {
            array_push($partsEncoded, rawurlencode(urldecode($part)));
        }

        $path = implode("/", $partsEncoded);

        return $path;
    }
}