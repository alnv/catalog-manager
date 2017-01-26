<?php

$GLOBALS['TL_DCA']['tl_module']['palettes']['catalogUniversalView'] = '{title_legend},name,headline,type;{catalog_legend},catalogTablename;{orderBy_legend},catalogOrderBy;{pagination_legend},catalogLimit,catalogPerPage;{master_legend},catalogPreventMasterView,catalogMasterTemplate;{template_legend},catalogTemplate,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogTablename'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogTablename'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
        'mandatory' => true,
        'doNotCopy' => true,
        'blankOptionLabel' => '-',
        'includeBlankOption'=>true,
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getCatalogs' ],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogTemplate'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 32,
        'tl_class' => 'w50',
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getCatalogTemplates' ],

    'exclude' => true,
    'sql' => "varchar(32) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogPreventMasterView'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogPreventMasterView'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr',
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMasterTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMasterTemplate'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 32,
        'tl_class' => 'w50',
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getCatalogTemplates' ],

    'exclude' => true,
    'sql' => "varchar(32) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogOrderBy'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogOrderBy'],
    'inputType' => 'keyValueWizard', // @todo

    'eval' => [

        //
    ],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogLimit'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogLimit'],
    'inputType' => 'text',
    'default' => 1000,

    'eval' => [

        'rgxp'=>'natural',
        'tl_class'=>'w50'
    ],

    'exclude' => true,
    'sql' => "smallint(5) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogPerPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogPerPage'],
    'inputType' => 'text',
    'default' => 0,

    'eval' => [

        'rgxp'=>'natural',
        'tl_class'=>'w50'
    ],

    'exclude' => true,
    'sql' => "smallint(5) unsigned NOT NULL default '0'"
];