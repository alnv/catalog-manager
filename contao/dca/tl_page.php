<?php

use Alnv\CatalogManagerBundle\Classes\tl_page;

$GLOBALS['TL_DCA']['tl_page']['config']['onload_callback'][] = [tl_page::class, 'removeRouting'];
$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][] = [tl_page::class, 'setRoutingParameter'];

$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'catalogUseMaster';
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'catalogUseChangeLanguage';

$GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] = str_replace( 'includeLayout;', 'includeLayout;{catalogSettings_legend},catalogUseMaster,catalogUseChangeLanguage;', $GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] );

$GLOBALS['TL_DCA']['tl_page']['subpalettes']['catalogUseChangeLanguage'] = 'catalogChangeLanguageTable';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['catalogUseMaster'] = 'catalogMasterTable,catalogShowInBreadcrumb';

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogUseMaster'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr',
        'submitOnChange' => true
    ],
    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogShowInBreadcrumb'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr'
    ],
    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogUseChangeLanguage'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr',
        'submitOnChange' => true
    ],
    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogChangeLanguageTable'] = [
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
    'options_callback' => ['CatalogManager\classes\tl_page', 'getCatalogTables' ],
    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_page']['fields']['catalogMasterTable'] = [
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
    'options_callback' => [tl_page::class, 'getCatalogTables' ],
    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];