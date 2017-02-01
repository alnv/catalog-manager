<?php

$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = [ 'CatalogManager\tl_module', 'disableNotRequiredFields' ];

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogStoreFile';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseViewPage';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseMasterPage';

$GLOBALS['TL_DCA']['tl_module']['palettes']['catalogUniversalView'] = '{title_legend},name,headline,type;{catalog_legend},catalogTablename;{catalogView_legend},catalogUseViewPage;{orderBy_legend},catalogOrderBy;{pagination_legend},catalogLimit,catalogPerPage;{master_legend},catalogUseMasterPage,catalogMasterTemplate,catalogPreventMasterView;{join_legend},catalogJoinFields,catalogJoinParentTable;{relation_legend},catalogRelatedChildTables,catalogRelatedParentTable;{frontend_editing_legend},tableless,disableCaptcha,catalogNoValidate,catalogFormTemplate,catalogStoreFile,catalogItemOperations,catalogFormRedirect;{template_legend},catalogTemplate,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseViewPage'] = 'catalogViewPage';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseMasterPage'] = 'catalogMasterPage';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogStoreFile'] = 'catalogUploadFolder,catalogUseHomeDir,catalogDoNotOverwrite';

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogTablename'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogTablename'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
        'mandatory' => true,
        'doNotCopy' => true,
        'submitOnChange' => true,
        'blankOptionLabel' => '-',
        'includeBlankOption'=>true,
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getCatalogs' ],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseViewPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseViewPage'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogViewPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogViewPage'],
    'inputType' => 'pageTree',

    'eval' => [

        'tl_class' => 'clr',
        'mandatory' => true,
        'fieldType' => 'radio',
    ],

    'foreignKey' => 'tl_page.title',

    'relation' => [

        'load' => 'lazy',
        'type' => 'hasOne'
    ],

    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogTemplate'],
    'inputType' => 'select',
    'default' => 'catalog_teaser',

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

        'tl_class' => 'w50 m12',
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseMasterPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseMasterPage'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMasterPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMasterPage'],
    'inputType' => 'pageTree',

    'eval' => [

        'tl_class' => 'clr',
        'mandatory' => true,
        'fieldType' => 'radio',
    ],

    'foreignKey' => 'tl_page.title',

    'relation' => [

        'load' => 'lazy',
        'type' => 'hasOne'
    ],

    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
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

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogJoinFields'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogJoinFields'],
    'inputType' => 'checkbox',

    'eval' => [

        'multiple' => true,
        'maxlength' => 255,
        'tl_class' => 'w50',
        'doNotCopy' => true,
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getJoinAbleFields' ],

    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogJoinParentTable'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogJoinParentTable'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12',
        'doNotCopy' => true,
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogRelatedChildTables'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogRelatedChildTables'],
    'inputType' => 'checkbox',

    'eval' => [

        'multiple' => true,
        'maxlength' => 255,
        'tl_class' => 'w50',
        'doNotCopy' => true,
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getChildTables' ],

    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogRelatedParentTable'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogRelatedParentTable'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 255,
        'doNotCopy' => true,
        'tl_class' => 'w50 m12',
        'blankOptionLabel' => '-',
        'includeBlankOption'=>true,
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getParentTable' ],

    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogFormTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogFormTemplate'],
    'inputType' => 'select',
    'default' => 'form_catalog_default',

    'eval' => [

        'chosen' => true,
        'maxlength' => 32,
        'tl_class' => 'w50',
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getCatalogFormTemplates' ],

    'exclude' => true,
    'sql' => "varchar(32) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogItemOperations'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogItemOperations'],
    'inputType' => 'checkbox',
    'default' => 'form_catalog_default',

    'eval' => [

        'multiple' => true,
        'maxlength' => 256,
        'tl_class' => 'w50 clr',
    ],

    'options_callback' => [ 'CatalogManager\tl_module', 'getCatalogOperationItems' ],

    'exclude' => true,
    'sql' => "varchar(256) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogStoreFile'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogStoreFile'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUploadFolder'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUploadFolder'],
    'inputType' => 'fileTree',

    'eval' => [

        'fieldType' => 'radio',
        'tl_class' => 'clr',
        'mandatory' => true
    ],

    'exclude' => true,
    'sql' => "binary(16) NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseHomeDir'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseHomeDir'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogDoNotOverwrite'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogDoNotOverwrite'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogNoValidate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogNoValidate'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['disableCaptcha']['eval']['tl_class'] = 'w50';

$GLOBALS['TL_DCA']['tl_module']['fields']['tableless']['eval']['tl_class'] = 'w50';

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogFormRedirect'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogFormRedirect'],
    'inputType' => 'pageTree',

    'eval' => [

        'tl_class' => 'w50',
        'fieldType' => 'radio',
    ],

    'foreignKey' => 'tl_page.title',

    'relation' => [

        'load' => 'lazy',
        'type' => 'hasOne'
    ],

    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];