<?php

$GLOBALS['TL_DCA']['tl_content']['palettes']['catalogFilterForm'] = '{type_legend},type,headline;{include_legend},catalogForm;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['catalogSocialSharingButtons'] = '{type_legend},type;{social_sharing_legend},catalogSocialSharingTable,catalogSocialSharingTitle,catalogSocialSharingDescription,catalogSocialSharingTemplate,catalogSocialSharingButtons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogForm'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogForm'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'mandatory' => true,
        'submitOnChange' => true,
        'blankOptionLabel' => '-',
        'tl_class' => 'w50 wizard',
        'includeBlankOption' => true,
    ],

    'wizard' => [

        [
            'CatalogManager\tl_content', 'editCatalogForm'
        ]
    ],

    'options_callback' => [ 'CatalogManager\tl_content', 'getCatalogForms' ],

    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogSocialSharingTable'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogSocialSharingTable'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'mandatory' => true,
        'tl_class' => 'w50',
        'submitOnChange' => true,
        'includeBlankOption' => true
    ],

    'options_callback' => [ 'CatalogManager\tl_content', 'getCatalogTables' ],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogSocialSharingTitle'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogSocialSharingTitle'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
        'includeBlankOption' => true
    ],

    'options_callback' => [ 'CatalogManager\tl_content', 'getCatalogFields' ],
    
    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogSocialSharingDescription'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogSocialSharingDescription'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
        'includeBlankOption' => true
    ],

    'options_callback' => [ 'CatalogManager\tl_content', 'getCatalogFields' ],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogSocialSharingButtons'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogSocialSharingButtons'],
    'inputType' => 'checkboxWizard',

    'eval' => [

        'multiple' => true,
        'tl_class' => 'clr',
    ],

    'reference' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['sharingButtons'],
    'options_callback' => [ 'CatalogManager\tl_content', 'getSocialSharingButtons' ],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogSocialSharingTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogSocialSharingTemplate'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50'
    ],

    'options_callback' => [ 'CatalogManager\tl_content', 'getSocialSharingTemplates' ],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

if ( \Input::get('do') && \Input::get('ctlg_table') ) {
    
    $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ \Input::get('ctlg_table') ];

    if ( is_array( $arrCatalog ) && !empty( $arrCatalog ) ) {

        if ( $arrCatalog['tablename'] ) {

            $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = $arrCatalog['tablename'];
        }
    }
}