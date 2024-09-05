<?php

use Contao\DC_Table;
use Alnv\CatalogManagerBundle\Classes\tl_catalog;

$GLOBALS['TL_DCA']['tl_catalog'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => ['tl_catalog_fields'],
        'onload_callback' => [
            [tl_catalog::class, 'checkLicense'],
            [tl_catalog::class, 'checkPermission'],
            [tl_catalog::class, 'checkEditMask']
        ],
        'onsubmit_callback' => [
            [tl_catalog::class, 'createTableOnSubmit'],
            [tl_catalog::class, 'setCoreTableData']
        ],
        'ondelete_callback' => [
            [tl_catalog::class, 'dropTableOnDelete']
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],
    'list' => [
        'sorting' => [
            'mode' => 2,
            'flag' => 1,
            'fields' => ['name'],
            'panelLayout' => 'filter;sort,search,limit'
        ],
        'label' => [
            'showColumns' => true,
            'fields' => ['name', 'tablename', 'info']
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg'
            ],
            'editFields' => [
                'href' => 'table=tl_catalog_fields',
                'icon' => 'children.svg'
            ],
            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.svg'
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.gif'
            ]
        ],
        'global_operations' => [
            'license' => [
                'label' => &$GLOBALS['TL_LANG']['tl_catalog']['license'],
                'href' => 'table=tl_catalog_license',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ],
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ]
    ],
    'palettes' => [
        '__selector__' => ['type', 'mode', 'isBackendModule', 'useGeoCoordinates', 'addressInputType', 'useChangeLanguage', 'languageEntitySource', 'useRedirect', 'useOwnLabelFormat', 'useOwnGroupFormat', 'enableLanguageBar'],
        'default' => '{table_settings},type,tablename,cTables,pTable,addContentElements,useVC;{description_settings},name,info,description;{dcSorting_settings},mode;{dcLabel_settings},labelFields,showColumns,format;{navigation_legend},isBackendModule;{operations_legend},operations;{panel_layout_legend},panelLayout;{label_format_legend:hide},useOwnLabelFormat;{group_format_legend:hide},useOwnGroupFormat;{permission_legend:hide},permissionType;{redirect_legend:hide},useRedirect;{field_settings_legend:hide},titleIsMandatory;{geoCoordinates_legend:hide},useGeoCoordinates;{changeLanguageModule_legend:hide},useChangeLanguage',
        'modifier' => '{table_settings},type,tablename,cTables,pTable,addContentElements,useVC;{description_settings},name,info,description;{dcSorting_settings},mode;{dcLabel_settings},labelFields,showColumns,format;{panel_layout_legend},panelLayout;{geoCoordinates_legend:hide},useGeoCoordinates;'
    ],
    'subpalettes' => [
        'useOwnLabelFormat' => 'labelFormat',
        'useOwnGroupFormat' => 'groupFormat',
        'addressInputType_useSingleField' => 'geoAddress',
        'enableLanguageBar' => 'fallbackLanguage,languages',
        'isBackendModule' => 'modulename,navArea,navPosition',
        'useRedirect' => 'internalUrlColumn,externalUrlColumn',
        'useGeoCoordinates' => 'latField,lngField,addressInputType',
        'languageEntitySource_parentTable' => 'languageEntityColumn',
        'useChangeLanguage' => 'linkEntityColumn,languageEntitySource',
        'languageEntitySource_currentTable' => 'languageEntityColumn,enableLanguageBar',
        'addressInputType_useMultipleFields' => 'geoStreet,geoStreetNumber,geoPostal,geoCity,geoCountry',
        'mode_0' => '',
        'mode_3' => '',
        'mode_5' => '',
        'mode_2' => 'sortingFields',
        'mode_6' => 'sortingFields',
        'mode_1' => 'sortingFields,flag',
        'mode_4' => 'headerFields,sortingFields,flag'
    ],
    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'type' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['type'],
            'inputType' => 'select',
            'default' => 'default',
            'eval' => [
                'chosen' => true,
                'tl_class' => 'w50',
                'submitOnChange' => true
            ],
            'options' => ['default', 'modifier'],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog']['reference']['type'],
            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],
        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['name'],
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 128,
                'doNotCopy' => true,
                'tl_class' => 'w50',
            ],
            'search' => true,
            'sorting' => true,
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'description' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['description'],
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 512,
                'doNotCopy' => true,
                'tl_class' => 'long clr',
            ],
            'exclude' => true,
            'sql' => "varchar(512) NOT NULL default ''"
        ],

        'info' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['info'],
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 16,
                'tl_class' => 'w50',
                'doNotCopy' => true
            ],
            'search' => true,
            'exclude' => true,
            'sql' => "varchar(16) NOT NULL default ''"
        ],
        'tablename' => [
            'inputType' => 'text',
            'eval' => [
                'rgxp' => 'extnd',
                'maxlength' => 128,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
                'spaceToUnderscore' => true,
            ],
            'save_callback' => [
                [tl_catalog::class, 'checkTablename'],
                [tl_catalog::class, 'renameTable']
            ],
            'search' => true,
            'sorting' => true,
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'isBackendModule' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['isBackendModule'],
            'inputType' => 'checkbox',
            'eval' => [
                'doNotCopy' => true,
                'submitOnChange' => true,
            ],
            'save_callback' => [[tl_catalog::class, 'checkModeTypeForPTableAndModes']],
            'filter' => true,
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'mode' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['mode'],
            'inputType' => 'radio',
            'default' => '0',

            'eval' => [

                'tl_class' => 'clr',
                'mandatory' => true,
                'submitOnChange' => true,
            ],

            'options_callback' => [tl_catalog::class, 'getModeTypes'],
            'save_callback' => [[tl_catalog::class, 'checkModeTypeRequirements']],

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog']['reference']['mode'],

            'exclude' => true,
            'sql' => "varchar(2) NOT NULL default ''"
        ],

        'pTable' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['pTable'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50',
                'submitOnChange' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [tl_catalog::class, 'getSystemTables'],
            'save_callback' => [[tl_catalog::class, 'checkModeTypeForBackendModule']],

            'search' => true,
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'cTables' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['cTables'],
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'multiple' => true,
                'tl_class' => 'w50'
            ],

            'options_callback' => [tl_catalog::class, 'getSystemTables'],

            'search' => true,
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'flag' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['flag'],
            'inputType' => 'select',
            'default' => '2',
            'eval' => [
                'chosen' => true,
                'tl_class' => 'w50',
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options_callback' => [tl_catalog::class, 'getFlagTypes'],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog']['reference']['flag'],
            'exclude' => true,
            'sql' => "varchar(2) NOT NULL default ''"
        ],

        'format' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['format'],
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 128,
                'allowHtml' => true,
                'tl_class' => 'long clr',
            ],
            'save_callback' => [[tl_catalog::class, 'checkModeTypeForFormat']],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'headerFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['headerFields'],
            'inputType' => 'checkboxWizard',
            'eval' => [
                'multiple' => true,
                'tl_class' => 'clr',
                'maxlength' => 1024,
            ],
            'options_callback' => [tl_catalog::class, 'getParentDataContainerFields'],
            'exclude' => true,
            'sql' => "varchar(1024) NOT NULL default ''"
        ],

        'sortingFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['sortingFields'],
            'inputType' => 'checkboxWizard',
            'eval' => [
                'multiple' => true,
                'tl_class' => 'clr',
                'maxlength' => 1024,
            ],
            'options_callback' => [tl_catalog::class, 'getDataContainerFields'],
            'exclude' => true,
            'sql' => "varchar(1024) NOT NULL default ''"
        ],

        'labelFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['labelFields'],
            'inputType' => 'checkboxWizard',
            'eval' => [
                'multiple' => true,
                'tl_class' => 'clr',
                'maxlength' => 1024,
            ],
            'options_callback' => [tl_catalog::class, 'getDataContainerFields'],
            'exclude' => true,
            'sql' => "varchar(1024) NOT NULL default ''"
        ],

        'showColumns' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['showColumns'],
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50'
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'panelLayout' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['panelLayout'],
            'inputType' => 'checkbox',
            'eval' => [
                'multiple' => true
            ],
            'options_callback' => [tl_catalog::class, 'getPanelLayouts'],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog']['reference'],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'operations' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['operations'],
            'inputType' => 'checkbox',
            'eval' => [
                'multiple' => true
            ],
            'options_callback' => [tl_catalog::class, 'getOperations'],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog']['reference'],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'modulename' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['modulename'],
            'inputType' => 'text',
            'eval' => [
                'unique' => true,
                'rgxp' => 'extnd',
                'maxlength' => 128,
                'tl_class' => 'w50',
                'doNotCopy' => true,
                'spaceToUnderscore' => true,
            ],
            'save_callback' => [
                [tl_catalog::class, 'parseModulename'],
            ],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'navArea' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['navArea'],
            'inputType' => 'select',
            'default' => 'system',
            'eval' => [
                'chosen' => true,
                'tl_class' => 'w50',
                'doNotCopy' => true
            ],
            'options_callback' => [tl_catalog::class, 'getNavigationAreas'],
            'exclude' => true,
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'navPosition' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['navPosition'],
            'inputType' => 'select',
            'default' => '0',
            'eval' => [
                'chosen' => true,
                'tl_class' => 'w50',
                'doNotCopy' => true
            ],
            'options_callback' => [tl_catalog::class, 'getNavigationPosition'],
            'exclude' => true,
            'sql' => "varchar(2) NOT NULL default ''"
        ],

        'addContentElements' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['addContentElements'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'clr'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'useGeoCoordinates' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['useGeoCoordinates'],
            'inputType' => 'checkbox',

            'eval' => [

                'doNotCopy' => true,
                'tl_class' => 'clr',
                'submitOnChange' => true,
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'latField' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['latField'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [tl_catalog::class, 'getDataContainerFields'],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'lngField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['lngField'],
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options_callback' => [tl_catalog::class, 'getDataContainerFields'],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'addressInputType' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['addressInputType'],
            'inputType' => 'radio',
            'eval' => [
                'tl_class' => 'clr',
                'doNotCopy' => true,
                'mandatory' => true,
                'submitOnChange' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options' => ['useSingleField', 'useMultipleFields'],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog']['reference']['addressInputType'],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'geoAddress' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['geoAddress'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [tl_catalog::class, 'getDataContainerFields'],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'geoStreet' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['geoStreet'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50',
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [tl_catalog::class, 'getDataContainerFields'],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'geoStreetNumber' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['geoStreetNumber'],
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'tl_class' => 'w50',
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options_callback' => [tl_catalog::class, 'getDataContainerFields'],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'geoPostal' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['geoPostal'],
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'tl_class' => 'w50',
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options_callback' => [tl_catalog::class, 'getDataContainerFields'],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'geoCity' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['geoCity'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50',
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [tl_catalog::class, 'getDataContainerFields'],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'geoCountry' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['geoCountry'],
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'tl_class' => 'w50',
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options_callback' => [tl_catalog::class, 'getDataContainerFields'],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'useChangeLanguage' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['useChangeLanguage'],
            'inputType' => 'checkbox',
            'eval' => [
                'doNotCopy' => true,
                'tl_class' => 'clr',
                'submitOnChange' => true
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'languageEntitySource' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['languageEntitySource'],
            'inputType' => 'radio',
            'eval' => [
                'maxlength' => 16,
                'tl_class' => 'clr',
                'mandatory' => true,
                'doNotCopy' => true,
                'submitOnChange' => true
            ],
            'options' => ['parentTable', 'currentTable'],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog']['reference']['languageEntitySource'],
            'exclude' => true,
            'sql' => "varchar(16) NOT NULL default ''"
        ],

        'languageEntityColumn' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['languageEntityColumn'],
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options_callback' => [tl_catalog::class, 'getChangeLanguageColumns'],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'enableLanguageBar' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['enableLanguageBar'],
            'inputType' => 'checkbox',

            'eval' => [

                'doNotCopy' => true,
                'tl_class' => 'clr',
                'submitOnChange' => true
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'fallbackLanguage' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['fallbackLanguage'],
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'tl_class' => 'w50',
                'mandatory' => true,
                'submitOnChange' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options_callback' => [tl_catalog::class, 'getFallbackLanguages'],
            'exclude' => true,
            'sql' => "varchar(4) NOT NULL default ''"
        ],

        'languages' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['languages'],
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'multiple' => true,
                'tl_class' => 'w50',
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options_callback' => [tl_catalog::class, 'getSystemLanguages'],
            'exclude' => true,
            'sql' => "blob NULL"
        ],

        'linkEntityColumn' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['linkEntityColumn'],
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options_callback' => [tl_catalog::class, 'getDataContainerFields'],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'useRedirect' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['useRedirect'],
            'inputType' => 'checkbox',

            'eval' => [

                'doNotCopy' => true,
                'tl_class' => 'clr',
                'submitOnChange' => true
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'internalUrlColumn' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['internalUrlColumn'],
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options_callback' => [tl_catalog::class, 'getInternalCatalogFields'],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'externalUrlColumn' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['externalUrlColumn'],
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options_callback' => [tl_catalog::class, 'getExternalCatalogFields'],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'permissionType' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['permissionType'],
            'inputType' => 'radio',
            'eval' => [
                'maxlength' => 64,
                'tl_class' => 'clr',
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options_callback' => [tl_catalog::class, 'getPermissionTypes'],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog']['reference']['permissionType'],
            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'useOwnLabelFormat' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['useOwnLabelFormat'],
            'inputType' => 'checkbox',

            'eval' => [

                'doNotCopy' => true,
                'tl_class' => 'clr',
                'submitOnChange' => true
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'labelFormat' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['labelFormat'],
            'inputType' => 'textarea',

            'eval' => [

                'allowHtml' => true,
                'doNotCopy' => true,
                'tl_class' => 'clr',
            ],

            'exclude' => true,
            'sql' => "text NULL"
        ],

        'useOwnGroupFormat' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['useOwnGroupFormat'],
            'inputType' => 'checkbox',

            'eval' => [

                'doNotCopy' => true,
                'tl_class' => 'clr',
                'submitOnChange' => true
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'groupFormat' => [
            'inputType' => 'textarea',
            'eval' => [
                'allowHtml' => true,
                'doNotCopy' => true,
                'tl_class' => 'clr',
            ],
            'exclude' => true,
            'sql' => "text NULL"
        ],
        'useVC' => [
            'inputType' => 'checkbox',
            'eval' => [
                'doNotCopy' => true,
                'tl_class' => 'clr'
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],
        'titleIsMandatory' => [
            'inputType' => 'checkbox',
            'eval' => [
                'doNotCopy' => true,
                'tl_class' => 'w50 m12'
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ]
    ]
];