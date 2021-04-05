<?php

use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;
?>
<!doctype html>
<html lang="<?= LANGUAGE_ID ?>">
<head itemscope itemtype="http://schema.org/WPHeader">
    <title itemprop="headline"><?php $APPLICATION->ShowTitle() ?></title>

    <?php $APPLICATION->ShowHead();

    CJSCore::Init('jquery2');
    CJSCore::Init(['fx']);
    ?>

    <meta id="viewport" name="viewport" content="width=device-width,initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="<?= container()->get('assets.manager')->getEntry('main.css') ?>">

</head>
<body class="page page_<?= LANGUAGE_ID ?> page_<?php $APPLICATION->ShowProperty('page_type', 'secondary') ?>">
<div style="display: none;"><?php echo container()->get('icons.svg.load') ?></div>

<div class="wrapper">
    <?php
    $twig = container()->get('twig.instance');
    ?>

