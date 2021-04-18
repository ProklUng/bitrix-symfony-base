<?php

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

/* @global $APPLICATION CMain */
/* @global $USER CUser */

global $APPLICATION;
global $USER;

$result = [
    'uploaded' => 0,
];

if (!$USER->IsAuthorized()) {
    return json_encode([
        'error' => true,
        'message' => 'Authorized users only.'
    ]);
}

if (!empty($_FILES['upload'])) {
    $checkErr = CFile::CheckImageFile($_FILES['upload'], 0, 0, 0);

    if (empty($checkErr)) {
        $fileId = CFile::SaveFile($_FILES['upload'], 'ckeditor');
        if (!empty($fileId)) {
            $fileItem = CFile::GetFileArray($fileId);

            $result = [
                'fileName' => $fileItem['ORIGINAL_NAME'],
                'url'      => $fileItem['SRC'],
                'uploaded' => 1,
            ];
        }
    }
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($result);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php' ;