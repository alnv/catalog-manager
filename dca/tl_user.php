<?php

$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace( 'fop;', 'fop;{catalog_manager_legend},catalog,catalogp;{filterform_legend},filterform,filterformp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] );
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace( 'fop;', 'fop;{catalog_manager_legend},catalog,catalogp;{filterform_legend},filterform,filterformp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] );

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

$GLOBALS['TL_DCA']['tl_user']['fields']['filterform'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_user']['filterform'],
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_catalog_form.title',

    'eval' => [

        'multiple' => true
    ],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_user']['fields']['filterformp'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_user']['filterformp'],
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