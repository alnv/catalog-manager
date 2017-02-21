<?php

$GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] = str_replace( 'includeLayout;', 'includeLayout;{catalogMasterSettings_legend},catalogUseMaster;', $GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] );
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'catalogUseMaster';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['catalogUseMaster'] = 'catalogCatalogTable,catalogCatalogColumn';

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogUseMaster'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_page']['fields']['catalogUseMaster'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'm12 clr',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogCatalogTable'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_page']['fields']['catalogCatalogTable'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
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

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogCatalogColumn'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_page']['fields']['catalogCatalogColumn'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
        'mandatory' => true,
        'doNotCopy' => true,
        'submitOnChange' => true,
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],


    'options_callback' => [ 'CatalogManager\tl_page', 'getCatalogColumn' ],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];