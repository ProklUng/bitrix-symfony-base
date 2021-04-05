Использование:

<h2>Компонент news:</h2>

<?php

use Local\Bundles\BitrixComponentParamsBundle\Services\MakeArParams;
/**
 * @var MakeArParams $mapper
 */
$mapper = container()->get('bitrix_component_params_bundle.news_arparams');

$newsArParams = $mapper->make(
  [
      'IBLOCK_ID' => 1,
      'IBLOCK_TYPE' => 'content',
      'SEF_FOLDER' => '/clubs/',
      'CACHE_TIME' => '3600',
      'H1_PROPERTY' => $APPLICATION->GetTitle(false)
  ]
); ?>

Или через фасад:

<?php

use Local\Bundles\BitrixComponentParamsBundle\Facades\NewsParamsFacade;

$newsArParams = NewsParamsFacade::make(
    [
        'IBLOCK_ID' => 1,
        'IBLOCK_TYPE' => 'We_content',
        'SEF_FOLDER' => '/clubs/',
        'CACHE_TIME' => '3600',
        'H1_PROPERTY' => $APPLICATION->GetTitle(false)
    ]
);
?>

<h2>Компонент news.list:</h2>

<?php
use Local\Bundles\BitrixComponentParamsBundle\Services\MakeArParams;

/**
 * @var MakeArParams $mapper
 */
$mapper = container()->get('bitrix_component_params_bundle.news_list_arparams');

$newsArParams = $mapper->make(
  [
      'IBLOCK_ID' => 1,
      'IBLOCK_TYPE' => 'content',
      'CACHE_TIME' => '3600',
  ]
); ?>

Или через фасад:

<?php

use Local\Bundles\BitrixComponentParamsBundle\Facades\NewsListParamsFacade;

$newsArParams = NewsListParamsFacade::make(
    [
        'IBLOCK_ID' => 1,
        'IBLOCK_TYPE' => 'We_content',
        'SEF_FOLDER' => '/clubs/',
        'CACHE_TIME' => '3600',
        'H1_PROPERTY' => $APPLICATION->GetTitle(false)
    ]
);
?>
