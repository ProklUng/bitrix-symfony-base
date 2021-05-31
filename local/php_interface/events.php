<?php


// LastModified
use Prokl\BitrixOrdinaryToolsBundle\Services\Seo\Clearizer;
use Prokl\BitrixOrdinaryToolsBundle\Services\Seo\CMainHandlers;
use Prokl\BitrixOrdinaryToolsBundle\Services\Seo\SchemaOrg;

AddEventHandler(
    'main',
    'OnEpilog',
    [CMainHandlers::class, 'checkIfModifiedSince']
);

// Удаление HTML комментариев по рекомендации граждан из SEO.
AddEventHandler(
    'main',
    'OnEndBufferContent',
    [Clearizer::class, 'clearHtmlComments']
);

// itemprop для description.
AddEventHandler(
    'main',
    'OnEndBufferContent',
    [SchemaOrg::class, 'descriptionItemprop']
);
