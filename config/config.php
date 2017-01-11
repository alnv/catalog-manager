<?php

$GLOBALS['BE_MOD']['system']['catalog-manager'] = array(

    'name' => 'catalog-manager',

    'tables' => [
        
        'tl_catalog',
        'tl_catalog_fields'
    ]
);

$GLOBALS['TL_WRAPPERS']['stop'][] = 'fieldsetStop';
$GLOBALS['TL_WRAPPERS']['start'][] = 'fieldsetStart';

$GLOBALS['TL_HOOKS']['initializeSystem'][] = array( 'CatalogManager\InitializeSystem', 'initialize' );