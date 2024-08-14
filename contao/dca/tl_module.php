<?php

use Alnv\CatalogManagerBundle\Classes\tl_module;

$GLOBALS['TL_DCA']['tl_module']['config']['onsubmit_callback'][] = [tl_module::class, 'generateGeoCords'];
$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = [tl_module::class, 'checkModuleRequirements'];
$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = [tl_module::class, 'disableNotRequiredFields'];

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseMap';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'enableTableView';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogFastMode';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogStoreFile';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseViewPage';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseRelation';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseDownloads';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogAddMapInfoBox';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseMasterPage';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogAllowComments';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseTaxonomies';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseRadiusSearch';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseTaxonomyRedirect';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogEnableFrontendEditing';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseSocialSharingButtons';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogBookNavigationSortingType';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'catalogUseFrontendEditingViewPage';

$GLOBALS['TL_DCA']['tl_module']['palettes']['catalogBookNavigation'] = '{title_legend},name,headline,type;{catalog_legend},catalogTablename;{catalog_book_navigation_settings},catalogBookNavigationSortingType;{catalog_master_legend},catalogMasterPage;{catalog_taxonomy_legend:hide},catalogUseTaxonomies;{template_legend:hide},catalogCustomTemplate;{protected_legend:hide:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['catalogMasterView'] = '{title_legend},name,headline,type;{catalog_legend},catalogTablename;{catalog_master_legend},catalogSEOTitle,catalogSEODescription,catalogUseViewPage,catalogIgnoreVisibility;{catalog_taxonomy_legend},catalogUseTaxonomies,catalogEnableParentFilter,catalogActiveParameters;{download_legend:hide},catalogUseDownloads;{social_sharing_legend:hide},catalogUseSocialSharingButtons;{template_legend:hide},catalogMasterTemplate,catalogCustomTemplate;{catalog_join_legend:hide},catalogJoinFields,catalogJoinCTables,catalogJoinParentTable,catalogJoinAsArray;{catalog_relation_legend:hide},catalogUseRelation;{catalog_comments_legend:hide},catalogAllowComments;{catalog_json_legend:hide},catalogUseArray,catalogExcludeArrayOptions,catalogSendJsonHeader;{image_legend:hide},imgSize;{protected_legend:hide:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['catalogUniversalView'] = '{title_legend},name,headline,type;{catalog_legend},catalogTablename;{catalog_master_legend:hide},catalogSEOTitle,catalogSEODescription,catalogMasterTemplate,catalogPreventMasterView,catalogUseMasterPage,catalogUseViewPage;{catalog_view_legend},catalogDisableMasterLink,catalogShowQuantity,catalogAddContentElements,enableTableView,catalogFastMode,catalogIgnoreVisibility;{catalog_taxonomy_legend},catalogUseTaxonomies,catalogEnableParentFilter,catalogActiveParameters;{catalog_orderBy_legend:hide},catalogOrderBy,catalogGroupBy,catalogGroupHeadlineTag,catalogRandomSorting;{catalog_pagination_legend:hide},catalogAddPagination,catalogPerPage,catalogOffset;{catalog_join_legend:hide},catalogJoinFields,catalogJoinCTables,catalogJoinParentTable,catalogJoinAsArray;{catalog_relation_legend:hide},catalogUseRelation;{catalog_frontendEditing_legend:hide},catalogEnableFrontendEditing,catalogStoreFile,catalogUseFrontendEditingViewPage;{catalog_map_legend:hide},catalogUseMap;{catalog_radiusSearch_legend:hide},catalogUseRadiusSearch;{download_legend:hide},catalogUseDownloads;{social_sharing_legend:hide},catalogUseSocialSharingButtons;{catalog_comments_legend:hide},catalogAllowComments;{template_legend:hide},catalogTemplate,catalogCustomTemplate;{catalog_json_legend:hide},catalogUseArray,catalogExcludeArrayOptions,catalogSendJsonHeader;{image_legend:hide},imgSize;{protected_legend:hide:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseTaxonomies'] = 'catalogTaxonomies';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseRelation'] = 'catalogRelatedChildTables';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogAddMapInfoBox'] = 'catalogMapInfoBoxContent';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogFastMode'] = 'catalogPreventFieldFromFastMode';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseTaxonomyRedirect'] = 'catalogTaxonomyRedirect';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseViewPage'] = 'catalogViewPage,catalogAutoRedirect';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseFrontendEditingViewPage'] = 'catalogFrontendEditingViewPage';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseMasterPage'] = 'catalogMasterPage,catalogNoSearch,catalogSitemap';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogStoreFile'] = 'catalogUploadFolder,catalogUseHomeDir,catalogDoNotOverwrite';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseDownloads'] = 'catalogDownloads,catalogPdfOrientation,catalogPdfTemplate';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseRadiusSearch'] = 'catalogFieldLat,catalogFieldLng,catalogRadioSearchCountry';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['enableTableView'] = 'catalogActiveTableColumns,catalogTableViewTemplate,catalogTableBodyViewTemplate';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseSocialSharingButtons'] = 'catalogSocialSharingButtons,catalogSocialSharingHeadline,catalogSocialSharingCssID,catalogSocialSharingTemplate,catalogDisableSocialSharingCSS';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogAllowComments'] = 'com_template,catalogCommentSortOrder,catalogCommentPerPage,catalogCommentModerate,catalogCommentBBCode,catalogCommentRequireLogin,catalogCommentDisableCaptcha';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogUseMap'] = 'catalogMapAddress,catalogMapLat,catalogMapLng,catalogFieldLat,catalogFieldLng,catalogMapViewTemplate,catalogMapTemplate,catalogMapZoom,catalogMapType,catalogMapScrollWheel,catalogMapMarker,catalogAddMapInfoBox,catalogMapStyle';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogEnableFrontendEditing'] = 'catalogEnableFrontendPermission,disableCaptcha,catalogNoValidate,catalogFormTemplate,catalogItemOperations,catalogExcludedFields,catalogDefaultValues,catalogNotifyInsert,catalogNotifyDuplicate,catalogNotifyUpdate,catalogNotifyDelete,catalogFormRedirect,catalogFormRedirectParameter';

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogBookNavigationSortingType_manuel'] = '';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['catalogBookNavigationSortingType_custom'] = 'catalogBookNavigationItem,catalogOrderBy';

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogTablename'] = [
    'inputType' => 'select',
    'eval' => [
        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
        'mandatory' => true,
        'submitOnChange' => true,
        'blankOptionLabel' => '-',
        'includeBlankOption' => true,
    ],
    'options_callback' => [tl_module::class, 'getCatalogs'],
    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogActiveParameters'] = [
    'inputType' => 'text',
    'eval' => [
        'maxlength' => 255,
        'tl_class' => 'long'
    ],
    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogRoutingParameter'] = [
    'inputType' => 'checkboxWizard',
    'eval' => [
        'multiple' => true,
        'mandatory' => true,
        'tl_class' => 'clr',
    ],
    'options_callback' => [tl_module::class, 'getRoutingFields'],
    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogPageRouting'] = [
    'inputType' => 'radio',
    'eval' => [
        'mandatory' => true,
        'tl_class' => 'clr',
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],
    'options_callback' => [tl_module::class, 'getPageRouting'],
    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogBookNavigationSortingType'] = [
    'inputType' => 'radio',
    'eval' => [
        'tl_class' => 'clr',
        'mandatory' => true,
        'submitOnChange' => true,
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],
    'options' => ['manuel', 'custom'],
    'save_callback' => [[tl_module::class, 'checkSortingField']],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['reference']['catalogBookNavigationSortingType'],
    'exclude' => true,
    'sql' => "varchar(12) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogBookNavigationItem'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogBookNavigationItem'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'mandatory' => true,
        'tl_class' => 'w50 clr',
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options_callback' => [tl_module::class, 'getAllColumns'],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogFormRedirectParameter'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogFormRedirectParameter'],
    'inputType' => 'text',

    'eval' => [

        'maxlength' => 255,
        'tl_class' => 'clr w50',
        'decodeEntities' => true
    ],

    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseViewPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseViewPage'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogTaxonomyRedirect'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogTaxonomyRedirect'],
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

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogViewPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogViewPage'],
    'inputType' => 'pageTree',

    'eval' => [

        'tl_class' => 'w50',
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

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogAutoRedirect'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogAutoRedirect'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12',
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseDownloads'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseDownloads'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogDownloads'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogDownloads'],
    'inputType' => 'checkbox',

    'eval' => [

        'multiple' => true,
        'maxlength' => 128,
        'tl_class' => 'clr',
    ],

    'options_callback' => [tl_module::class, 'getCatalogDownloads'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['reference']['catalogDownloads'],
    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"

];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogPdfTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogPdfTemplate'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 64,
        'tl_class' => 'w50',
    ],

    'options_callback' => [tl_module::class, 'getPdfTemplates'],

    'exclude' => true,
    'sql' => "varchar(64) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogPdfOrientation'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogPdfOrientation'],
    'inputType' => 'select',

    'eval' => [

        'maxlength' => 2,
        'tl_class' => 'w50',
    ],

    'options' => ['P', 'L'],

    'exclude' => true,
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['reference']['catalogPdfOrientation'],
    'sql' => "varchar(2) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogShowQuantity'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogShowQuantity'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50',
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogTemplate'],
    'inputType' => 'select',
    'default' => 'ctlg_view_teaser',

    'eval' => [

        'chosen' => true,
        'maxlength' => 32,
        'tl_class' => 'w50',
    ],

    'options_callback' => [tl_module::class, 'getCatalogTemplates'],

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

        'tl_class' => 'clr',
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

    'options_callback' => [tl_module::class, 'getCatalogTemplates'],

    'exclude' => true,
    'sql' => "varchar(32) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogOrderBy'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogOrderBy'],
    'inputType' => 'catalogDuplexSelectWizard',

    'eval' => [

        'chosen' => true,
        'tl_class' => 'clr',
        'blankOptionLabel' => '-',
        'includeBlankOption' => true,
        'mainLabel' => 'catalogManagerFields',
        'dependedLabel' => 'catalogManagerOrder',
        'mainOptions' => ['CatalogManager\OrderByHelper', 'getSortableFields'],
        'dependedOptions' => ['CatalogManager\OrderByHelper', 'getOrderByItems']
    ],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogGroupBy'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogGroupBy'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options_callback' => [tl_module::class, 'getAllColumns'],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogGroupHeadlineTag'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogGroupHeadlineTag'],
    'inputType' => 'select',

    'eval' => [

        'maxlength' => 8,
        'tl_class' => 'w50',
    ],

    'options' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],

    'exclude' => true,
    'sql' => "varchar(8) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogRandomSorting'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogRandomSorting'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogAddPagination'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogAddPagination'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogOffset'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogOffset'],
    'inputType' => 'text',
    'default' => 0,

    'eval' => [

        'rgxp' => 'natural',
        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "smallint(5) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogPerPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogPerPage'],
    'inputType' => 'text',
    'default' => 0,

    'eval' => [

        'rgxp' => 'natural',
        'tl_class' => 'w50'
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
        'tl_class' => 'clr',
    ],

    'options_callback' => [tl_module::class, 'getJoinAbleFields'],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogJoinAsArray'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogJoinAsArray'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogJoinParentTable'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogJoinParentTable'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogJoinCTables'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogJoinCTables'],
    'inputType' => 'checkbox',

    'eval' => [

        'multiple' => true,
        'maxlength' => 255,
        'tl_class' => 'clr',
    ],

    'options_callback' => [tl_module::class, 'getChildTablesByTablename'],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseRelation'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseRelation'],
    'inputType' => 'checkbox',

    'eval' => [

        'submitOnChange' => true,
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogRelatedChildTables'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogRelatedChildTables'],
    'inputType' => 'catalogRelationRedirectWizard',

    'eval' => [],

    'options_callback' => [tl_module::class, 'getChildTablesByTablename'],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogEnableParentFilter'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogEnableParentFilter'],
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr'
    ],
    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogFormTemplate'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogFormTemplate'],
    'inputType' => 'select',
    // 'default' => 'form_catalog_default',
    'eval' => [
        'chosen' => true,
        'maxlength' => 32,
        'tl_class' => 'w50',
    ],
    'options_callback' => [tl_module::class, 'getCatalogFormTemplates'],
    'exclude' => true,
    'sql' => "varchar(32) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogItemOperations'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogItemOperations'],
    'inputType' => 'checkbox',

    'eval' => [

        'multiple' => true,
        'maxlength' => 512,
        'tl_class' => 'clr',
    ],

    'options_callback' => [tl_module::class, 'getCatalogOperationItems'],

    'reference' => &$GLOBALS['TL_LANG']['tl_module']['reference']['catalogItemOperations'],

    'exclude' => true,
    'sql' => "varchar(512) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogStoreFile'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogStoreFile'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'm12 clr',
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

        'tl_class' => 'w50 m12'
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

$GLOBALS['TL_DCA']['tl_module']['fields']['disableCaptcha']['eval']['tl_class'] = 'w50 m12';

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogFormRedirect'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogFormRedirect'],
    'inputType' => 'pageTree',

    'eval' => [

        'tl_class' => 'clr',
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

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogEnableFrontendPermission'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogEnableFrontendPermission'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12',
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogAllowComments'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogAllowComments'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogCommentPerPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogCommentPerPage'],
    'inputType' => 'text',

    'eval' => [

        'rgxp' => 'natural',
        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "smallint(5) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogCommentSortOrder'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogCommentSortOrder'],
    'inputType' => 'select',
    'default' => 'ascending',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'options' => ['ascending', 'descending'],

    'reference' => &$GLOBALS['TL_LANG']['MSC'],

    'exclude' => true,
    'sql' => "varchar(32) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogCommentModerate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogCommentModerate'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogCommentBBCode'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogCommentBBCode'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogCommentRequireLogin'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogCommentRequireLogin'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogCommentDisableCaptcha'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogCommentDisableCaptcha'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseMap'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseMap'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapAddress'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapAddress'],
    'inputType' => 'text',

    'eval' => [

        'tl_class' => 'long',
    ],

    'exclude' => true,
    'sql' => "varchar(256) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapLat'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapLat'],
    'inputType' => 'text',

    'eval' => [

        'tl_class' => 'w50',
    ],

    'exclude' => true,
    'sql' => "varchar(256) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapLng'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapLng'],
    'inputType' => 'text',

    'eval' => [

        'tl_class' => 'w50',
    ],

    'exclude' => true,
    'sql' => "varchar(256) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogFieldLat'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogFieldLat'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'mandatory' => true,
        'tl_class' => 'w50',
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options_callback' => [tl_module::class, 'getCatalogFieldsByTablename'],

    'exclude' => true,
    'sql' => "char(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogFieldLng'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogFieldLng'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'mandatory' => true,
        'tl_class' => 'w50',
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options_callback' => [tl_module::class, 'getCatalogFieldsByTablename'],

    'exclude' => true,
    'sql' => "char(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapViewTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapViewTemplate'],
    'inputType' => 'select',
    'default' => 'map_catalog_default',

    'eval' => [

        'chosen' => true,
        'maxlength' => 255,
        'tl_class' => 'w50',
        'mandatory' => true
    ],

    'options_callback' => [tl_module::class, 'getMapViewTemplates'],

    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapTemplate'],
    'inputType' => 'select',
    'default' => 'ctlg_map_default',

    'eval' => [

        'chosen' => true,
        'maxlength' => 255,
        'tl_class' => 'w50',
        'mandatory' => true
    ],

    'options_callback' => [tl_module::class, 'getMapTemplates'],

    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapZoom'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapZoom'],
    'inputType' => 'select',
    'default' => 10,

    'eval' => [

        'chosen' => true,
        'tl_class' => 'w50'
    ],

    'options' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20],

    'exclude' => true,
    'sql' => "smallint(5) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapType'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapType'],
    'inputType' => 'select',
    'default' => 'HYBRID',

    'eval' => [

        'chosen' => true,
        'tl_class' => 'w50'
    ],

    'options' => ['ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN'],

    'reference' => &$GLOBALS['TL_LANG']['tl_module']['reference']['catalogMapType'],

    'exclude' => true,
    'sql' => "varchar(16) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapScrollWheel'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapScrollWheel'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapMarker'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapMarker'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapStyle'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapStyle'],
    'inputType' => 'textarea',
    'eval' => [
        'tl_class' => 'clr',
        'rte' => 'ace|html',
        'allowHtml' => true,
        'decodeEntities' => true
    ],
    'exclude' => true,
    'sql' => "text NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogAddMapInfoBox'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogAddMapInfoBox'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogMapInfoBoxContent'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogMapInfoBoxContent'],
    'inputType' => 'textarea',

    'eval' => [

        'rte' => 'ace|html',
        'tl_class' => 'clr',
        'allowHtml' => true
    ],

    'exclude' => true,
    'sql' => "text NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseRadiusSearch'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseRadiusSearch'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogRadioSearchCountry'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogRadioSearchCountry'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options_callback' => [tl_module::class, 'getSystemCountries'],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseTaxonomies'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseTaxonomies'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'm12 clr',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogTaxonomies'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogTaxonomies'],
    'inputType' => 'catalogTaxonomyWizard',

    'eval' => [

        'dcTable' => 'tl_module',
        'taxonomyTable' => [tl_module::class, 'getTaxonomyTable'],
        'taxonomyEntities' => [tl_module::class, 'getTaxonomyFields']
    ],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogSEODescription'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogSEODescription'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'tl_class' => 'w50',
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options_callback' => [tl_module::class, 'getCatalogFieldsByTablename'],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogSEOTitle'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogSEOTitle'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'tl_class' => 'w50',
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options_callback' => [tl_module::class, 'getCatalogFieldsByTablename'],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogExcludedFields'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogExcludedFields'],
    'inputType' => 'checkbox',

    'eval' => [

        'multiple' => true,
        'tl_class' => 'clr'
    ],

    'options_callback' => [tl_module::class, 'getExcludedCatalogFields'],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogDisableMasterLink'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogDisableMasterLink'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['enableTableView'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['enableTableView'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogActiveTableColumns'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogActiveTableColumns'],
    'inputType' => 'checkboxWizard',

    'eval' => [

        'multiple' => true,
        'tl_class' => 'clr'
    ],

    'options_callback' => [tl_module::class, 'getAllColumns'],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogTableViewTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogTableViewTemplate'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'tl_class' => 'w50',
        'mandatory' => true
    ],

    'options_callback' => [tl_module::class, 'getTableViewTemplates'],

    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];


$GLOBALS['TL_DCA']['tl_module']['fields']['catalogTableBodyViewTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogTableBodyViewTemplate'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'tl_class' => 'w50',
        'mandatory' => true
    ],

    'options_callback' => [tl_module::class, 'getTableBodyViewTemplates'],

    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogEnableFrontendEditing'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogEnableFrontendEditing'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseFrontendEditingViewPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseFrontendEditingViewPage'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr w50',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogFrontendEditingViewPage'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogFrontendEditingViewPage'],
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

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseArray'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseArray'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr',
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogExcludeArrayOptions'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogExcludeArrayOptions'],
    'inputType' => 'checkbox',

    'eval' => [

        'multiple' => true,
        'tl_class' => 'clr',
    ],

    'options_callback' => [tl_module::class, 'getArrayOptions'],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogSendJsonHeader'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogSendJsonHeader'],
    'inputType' => 'radio',

    'eval' => [

        'maxlength' => 16,
        'tl_class' => 'clr',
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options' => ['permanent', 'onAjaxCall'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['reference']['catalogSendJsonHeader'],

    'exclude' => true,
    'sql' => "varchar(16) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyInsert'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogNotifyInsert'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'tl_class' => 'w50',
        'includeBlankOption' => true,
        'ncNotificationChoices' => ['ctlg_entity_status_insert']
    ],

    'options_callback' => [tl_module::class, 'getNotificationChoices'],

    'relation' => [

        'load' => 'lazy',
        'type' => 'hasOne',
        'table' => 'tl_nc_notification'
    ],

    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDuplicate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogNotifyDuplicate'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'tl_class' => 'w50',
        'includeBlankOption' => true,
        'ncNotificationChoices' => ['ctlg_entity_status_duplicate']
    ],

    'options_callback' => [tl_module::class, 'getNotificationChoices'],

    'relation' => [

        'load' => 'lazy',
        'type' => 'hasOne',
        'table' => 'tl_nc_notification'
    ],

    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyUpdate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogNotifyUpdate'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'tl_class' => 'w50',
        'includeBlankOption' => true,
        'ncNotificationChoices' => ['ctlg_entity_status_update']
    ],

    'options_callback' => [tl_module::class, 'getNotificationChoices'],

    'relation' => [

        'load' => 'lazy',
        'type' => 'hasOne',
        'table' => 'tl_nc_notification'
    ],

    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDelete'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogNotifyDelete'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'tl_class' => 'w50',
        'includeBlankOption' => true,
        'ncNotificationChoices' => ['ctlg_entity_status_delete']
    ],

    'options_callback' => [tl_module::class, 'getNotificationChoices'],

    'relation' => [

        'load' => 'lazy',
        'type' => 'hasOne',
        'table' => 'tl_nc_notification'
    ],

    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogCustomTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogCustomTemplate'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'tl_class' => 'w50',
        'maxlength' => 64,
        'includeBlankOption' => true
    ],

    'options_callback' => [tl_module::class, 'getCustomTemplate'],

    'exclude' => true,
    'sql' => "varchar(64) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogFastMode'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogFastMode'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12',
        'submitOnChange' => true
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogPreventFieldFromFastMode'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogPreventFieldFromFastMode'],
    'inputType' => 'checkbox',

    'eval' => [

        'multiple' => true,
        'tl_class' => 'clr'
    ],

    'options_callback' => [tl_module::class, 'getFastModeFields'],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogDefaultValues'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogDefaultValues'],
    'inputType' => 'catalogValueSetterWizard',

    'eval' => [

        'tl_class' => 'clr',
        'blankOptionLabel' => '-',
        'includeBlankOption' => true,
        'getKeys' => [tl_module::class, 'getKeyColumns'],
    ],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogUseSocialSharingButtons'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogUseSocialSharingButtons'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'clr',
        'submitOnChange' => true,
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogSocialSharingButtons'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogSocialSharingButtons'],
    'inputType' => 'checkboxWizard',

    'eval' => [

        'multiple' => true,
        'tl_class' => 'clr',
    ],

    'options_callback' => [tl_module::class, 'getSocialSharingButtons'],
    'reference' => &$GLOBALS['TL_LANG']['MSC']['sharingButtons'],

    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogSocialSharingTemplate'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogSocialSharingTemplate'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50'
    ],

    'options_callback' => [tl_module::class, 'getSocialSharingTemplates'],

    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogDisableSocialSharingCSS'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogDisableSocialSharingCSS'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogSocialSharingHeadline'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogSocialSharingHeadline'],
    'inputType' => 'inputUnit',

    'eval' => [

        'maxlength' => 200,
        'tl_class' => 'w50'
    ],

    'options' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'],

    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogSocialSharingCssID'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogSocialSharingCssID'],
    'inputType' => 'text',

    'eval' => [

        'tl_class' => 'w50',
        'multiple' => true,
        'size' => 2
    ],

    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogNoSearch'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogNoSearch'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50 m12'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogSitemap'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogSitemap'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'maxlength' => 32,
        'tl_class' => 'w50',
        'blankOptionLabel' => '-',
        'includeBlankOption' => true
    ],

    'options' => ['map_default', 'map_never'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['reference']['catalogSitemap'],

    'exclude' => true,
    'sql' => "varchar(32) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogAddContentElements'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogAddContentElements'],
    'inputType' => 'checkbox',

    'eval' => [

        'tl_class' => 'w50'
    ],

    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['catalogIgnoreVisibility'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['catalogIgnoreVisibility'],
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'w50 m12'
    ],
    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];