<?php

$GLOBALS['BE_MOD']['system']['catalog-manager'] = [

    'name' => 'catalog-manager',

    'tables' => [
        
        'tl_catalog',
        'tl_catalog_fields'
    ]
];

$GLOBALS['TL_HOOKS']['initializeSystem'][] =[ 'CatalogManager\InitializeCatalogManager', 'initialize' ];

$GLOBALS['TL_WRAPPERS']['stop'][] = 'fieldsetStop';
$GLOBALS['TL_WRAPPERS']['start'][] = 'fieldsetStart';

$GLOBALS['TL_PERMISSIONS'][] = 'catalog';
$GLOBALS['TL_PERMISSIONS'][] = 'catalogp';