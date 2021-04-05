<?php

use Local\Seo\Clearizer;
use Local\Seo\CMainHandlers;
use Local\Seo\SchemaOrg;

// LastModified
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
