<?php

$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace( 'fop;', 'fop;{catalog_manager_legend},catalog,catalogp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] );
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace( 'fop;', 'fop;{catalog_manager_legend},catalog,catalogp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] );

$GLOBALS['TL_DCA']['tl_user']['fields']['catalog'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_user']['catalog'],
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_catalog.name',

    'eval' => [

        'multiple' => true
    ],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_user']['fields']['catalogp'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_user']['catalogp'],
    'inputType' => 'checkbox',

    'options' => [

        'edit',
        'create',
        'delete'
    ],

    'eval' => [

        'multiple' => true
    ],

    'reference' => &$GLOBALS['TL_LANG']['tl_user']['reference'],

    'exclude' => true,
    'sql' => "blob NULL"
];