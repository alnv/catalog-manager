<?php

$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][] = [ 'CatalogManager\tl_page', 'setRoutingParameter' ];

$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'catalogUseMaster';
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'catalogUseRouting';
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'catalogUseChangeLanguage';

$GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] = str_replace( 'includeLayout;', 'includeLayout;{catalogSettings_legend},catalogUseMaster,catalogUseChangeLanguage,catalogUseRouting;', $GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] );

$GLOBALS['TL_DCA']['tl_page']['subpalettes']['catalogUseChangeLanguage'] = 'catalogChangeLanguageTable';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['catalogUseMaster'] = 'catalogMasterTable,catalogShowInBreadcrumb';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['catalogUseRouting'] = 'catalogRoutingTable,catalogRoutingParameter,catalogSetAutoItem,catalogRouting';

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogUseMaster'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_page']['catalogUseMaster'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogShowInBreadcrumb'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_page']['catalogShowInBreadcrumb'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogUseChangeLanguage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_page']['catalogUseChangeLanguage'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogUseRouting'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_page']['catalogUseRouting'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr',
        'doNotCopy' => true,
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogRoutingParameter'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_page']['catalogRoutingParameter'],
    'inputType' => 'checkboxWizard',

    'eval' => [

        'multiple' => true,
        'tl_class' => 'clr',
        'mandatory' => true,
        'doNotCopy' => true
    ],

    'options_callback' => [ 'CatalogManager\tl_page', 'getRoutingFields' ],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogSetAutoItem'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_page']['catalogSetAutoItem'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr',
        'doNotCopy' => true,
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogRouting'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_page']['catalogRouting'],
    'inputType' => 'text',

    'eval' => [

        'readonly' => true,
        'doNotCopy' => true,
        'tl_class' => 'long'
    ],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogChangeLanguageTable'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_page']['catalogChangeLanguageTable'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'clr',
        'mandatory' => true,
        'doNotCopy' => true,
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options_callback' => [ 'CatalogManager\tl_page', 'getCatalogTables' ],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogMasterTable'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_page']['catalogMasterTable'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'clr',
        'mandatory' => true,
        'doNotCopy' => true,
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],
    
    'options_callback' => [ 'CatalogManager\tl_page', 'getCatalogTables' ],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogRoutingTable'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_page']['catalogRoutingTable'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'clr',
        'mandatory' => true,
        'doNotCopy' => true,
        'submitOnChange' => true,
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options_callback' => [ 'CatalogManager\tl_page', 'getCatalogTables' ],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];