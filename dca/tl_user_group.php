<?php

$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace( 'fop;', 'fop;{catalog_manager_legend},catalog,catalogp;{filterform_legend},filterform,filterformp;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] );

$GLOBALS['TL_DCA']['tl_user_group']['fields']['catalog'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_user_group']['catalog'],
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_catalog.name',

    'eval' => [

        'multiple' => true
    ],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['catalogp'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_user_group']['catalogp'],
    'inputType' => 'checkbox',

    'options' => [

        'edit',
        'create',
        'delete'
    ],

    'eval' => [

        'multiple' => true
    ],

    'reference' => &$GLOBALS['TL_LANG']['tl_user_group']['reference'],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['filterform'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_user_group']['filterform'],
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_catalog_form.title',

    'eval' => [

        'multiple' => true
    ],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['filterformp'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_user_group']['filterformp'],
    'inputType' => 'checkbox',

    'options' => [

        'edit',
        'create',
        'delete'
    ],

    'eval' => [

        'multiple' => true
    ],

    'reference' => &$GLOBALS['TL_LANG']['tl_user_group']['reference'],

    'exclude' => true,
    'sql' => "blob NULL"
];