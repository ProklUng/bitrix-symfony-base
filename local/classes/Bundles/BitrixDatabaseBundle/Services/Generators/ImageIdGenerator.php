<?php

namespace Local\Bundles\BitrixDatabaseBundle\Services\Generators;

use CFile;
use COption;
use Exception;
use Local\Bundles\BitrixDatabaseBundle\Services\Generators\Abstraction\AbstractGenerator;

/**
 * Class ImageIdGenerator
 * @package Local\Bundles\BitrixDatabaseBundle\Services\Generators
 *
 * @since 08.04.2021
 */
class ImageIdGenerator extends AbstractGenerator
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generate(?array $payload = null)
    {
        $result = [];

        $uploadDir = COption::GetOptionString('main', 'upload_dir','upload');

        $query = CFile::GetList([], ['MODULE_ID' => 'iblock']);
        while ($arResult = $query->GetNext()) {
            $filename = $_SERVER['DOCUMENT_ROOT'] .
                    '/'
                    .
                    $uploadDir
                    .
                    '/'
                    . $arResult['SUBDIR'] .
                    '/'
                    .$arResult['FILE_NAME'];

            if (CFile::IsImage($filename)) {
                $result[] = (int)$arResult['ID'];
            }
        }

        return $result[random_int(0, count($result) - 1)];
    }
}
