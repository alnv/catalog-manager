<?php

$GLOBALS['BE_MOD']['system']['catalog-maker'] = array(

    'name' => 'catalog-maker',

    'tables' => [
        
        'tl_catalog',
        'tl_catalog_fields'
    ]
);

$GLOBALS['TL_WRAPPERS']['start'][] = 'fieldsetStart';
$GLOBALS['TL_WRAPPERS']['stop'][] = 'fieldsetStop';