<?php

$GLOBALS['BE_MOD']['system']['catalog-manager'] = [

    'name' => 'catalog-manager',

    'tables' => [
        
        'tl_catalog',
        'tl_catalog_fields'
    ]
];

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = [ 'CatalogManager\UserPermissionExtension', 'initialize' ];
$GLOBALS['TL_HOOKS']['initializeSystem'][] =[ 'CatalogManager\CatalogManagerInitializer', 'initialize' ];

$GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'] = [];

$GLOBALS['TL_WRAPPERS']['stop'][] = 'fieldsetStop';
$GLOBALS['TL_WRAPPERS']['start'][] = 'fieldsetStart';

$GLOBALS['TL_PERMISSIONS'][] = 'catalog';
$GLOBALS['TL_PERMISSIONS'][] = 'catalogp';