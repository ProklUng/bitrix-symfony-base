<?php

use Prokl\Ckeditor\Medialib;

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

/* @global $APPLICATION CMain */
/* @global $USER CUser */

global $APPLICATION;
global $USER;

if (!$USER->IsAuthorized()) {
    return json_encode([
        'error' => true,
        'message' => 'Authorized users only.'
    ]);
}

$pageNum = !empty($_REQUEST['page_num']) ? intval($_REQUEST['page_num']) : 1;
$collectionId = !empty($_REQUEST['collection_id']) ? intval($_REQUEST['collection_id']) : 0;

$collections = MediaLib::getCollections();
array_unshift(
    $collections,
    [
        'ID'   => 0,
        'NAME' => 'нет',
    ]
);

$result = [
    'collections'   => $collections,
    'collection_id' => 0,
    'items'         => [],
    'page_count'    => 0,
    'page_num'      => 1,
];

if ($collectionId > 0) {
    $elements = Medialib::getElements(
        [
            'collection_id' => $collectionId,
        ],
        [
            'page_size' => 15,
            'page_num'  => $pageNum,
        ],
        [
            'width'  => 50,
            'height' => 50,
            'exact'  => 1,
        ],
        [
            'width'  => 1024,
            'height' => 768,
            'exact'  => 0,
        ]
    );

    $result['collection_id'] = $collectionId;
    $result['items'] = $elements['items'];
    $result['page_num'] = $elements['page_num'];
    $result['page_count'] = $elements['page_count'];
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($result);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';