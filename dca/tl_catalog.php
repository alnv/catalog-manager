<?php

$GLOBALS['TL_DCA']['tl_catalog'] = [

    'config' => [

        'dataContainer' => 'Table',
        'ctable' => [ 'tl_catalog_fields' ],

        'onload_callback' => [

            [ 'CatalogManager\tl_catalog', 'checkPermission' ]
        ],

        'onsubmit_callback' => [

            [ 'CatalogManager\tl_catalog', 'createTableOnSubmit' ]
        ],

        'ondelete_callback' => [

            [ 'CatalogManager\tl_catalog', 'dropTableOnDelete' ]
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
            'fields' => [ 'name' ],
            'panelLayout' => 'filter;sort,search,limit'
        ],

        'label' => [

            'showColumns' => true,
            'fields' => [ 'name', 'tablename', 'info' ]
        ],

        'operations' => [

            'editFields' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog']['editFields'],
                'href' => 'table=tl_catalog_fields',
                'icon' => 'edit.gif'
            ],

            'edit' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog']['edit'],
                'href' => 'act=edit',
                'icon' => 'header.gif'
            ],

            'copy' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif'
            ],

            'delete' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],

            'show' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            ]
        ]
    ],

    'palettes' => [

        '__selector__' => [ 'isBackendModule', 'useGeoCoordinates', 'addressInputType', 'useChangeLanguage', 'languageEntitySource', 'useRedirect' ],
        'default' => '{table_settings},tablename,cTables,pTable,addContentElements;{description_settings},name,info,description;{sorting_settings},mode,flag,format,sortingFields,labelFields,headerFields,showColumns;{navigation_legend},isBackendModule;{operations_legend},operations;{panel_layout_legend},panelLayout;{permission_legend},permissionType;{redirect_legend:hide},useRedirect;{geoCoordinates_legend:hide},useGeoCoordinates;{changeLanguageModule_legend:hide},useChangeLanguage'
    ],

    'subpalettes' => [

        'isBackendModule' => 'navArea,navPosition',
        'addressInputType_useSingleField' => 'geoAddress',
        'useRedirect' => 'internalUrlColumn,externalUrlColumn',
        'useGeoCoordinates' => 'latField,lngField,addressInputType',
        'languageEntitySource_parentTable' => 'languageEntityColumn',
        'languageEntitySource_currentTable' => 'languageEntityColumn',
        'useChangeLanguage' => 'linkEntityColumn,languageEntitySource',
        'addressInputType_useMultipleFields' => 'geoStreet,geoStreetNumber,geoPostal,geoCity,geoCountry'
    ],

    'fields' => [

        'id' => [

            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],

        'tstamp' => [

            'sql' => "int(10) unsigned NOT NULL default '0'"
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

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['tablename'],
            'inputType' => 'text',

            'eval' => [

                'unique' => true,
                'rgxp' => 'extnd',
                'maxlength' => 128,
                'mandatory' => true,
                'doNotCopy' => true,
                'tl_class' => 'long',
                'spaceToUnderscore' => true,
            ],

            'save_callback' => [

                [ 'CatalogManager\tl_catalog', 'checkTablename' ],
                [ 'CatalogManager\tl_catalog', 'renameTable' ]
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

            'save_callback' => [ [ 'CatalogManager\tl_catalog', 'checkModeTypeForPTableAndModes' ] ],

            'filter' => true,
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'mode' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['mode'],
            'inputType' => 'select',
            'default' => '0',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50',
                'submitOnChange' => true,
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getModeTypes' ],
            'save_callback' => [ [ 'CatalogManager\tl_catalog', 'checkModeTypeRequirements' ] ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getAllPTables' ],
            'save_callback' => [ [ 'CatalogManager\tl_catalog', 'checkModeTypeForBackendModule' ] ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getAllCTables' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getFlagTypes' ],

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog']['reference']['flag'],

            'exclude' => true,
            'sql' => "varchar(2) NOT NULL default ''"
        ],

        'format' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['format'],
            'inputType' => 'text',

            'eval' => [

                'maxlength' => 128,
                'tl_class' => 'long clr',
                'allowHtml' => true
            ],

            'save_callback' => [ [ 'CatalogManager\tl_catalog', 'checkModeTypeForFormat' ] ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'headerFields' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['headerFields'],
            'inputType' => 'checkboxWizard',

            'eval' => [

                'chosen' => true,
                'multiple' => true,
                'tl_class' => 'clr',
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getParentDataContainerFields' ],

            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'sortingFields' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['sortingFields'],
            'inputType' => 'checkboxWizard',

            'eval' => [

                'chosen' => true,
                'multiple' => true,
                'tl_class' => 'clr',
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getDataContainerFields' ],

            'exclude' => true,
            'sql' => "varchar(1024) NOT NULL default ''"
        ],

        'labelFields' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['labelFields'],
            'inputType' => 'checkboxWizard',

            'eval' => [

                'chosen' => true,
                'multiple' => true,
                'tl_class' => 'clr',
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getDataContainerFields' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getPanelLayouts' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getOperations' ],

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog']['reference'],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getNavigationAreas' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getNavigationPosition' ],

            'exclude' => true,
            'sql' => "varchar(2) NOT NULL default ''"
        ],
        
        'addContentElements' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['addContentElements'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12'
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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getCatalogFields' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getCatalogFields' ],

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

            'options' => [ 'useSingleField', 'useMultipleFields' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getCatalogFields' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getCatalogFields' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getCatalogFields' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getCatalogFields' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getCatalogFields' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getCatalogFields' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'useChangeLanguage' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['useChangeLanguage'],
            'inputType' => 'checkbox',

            'eval' => [

                'doNotCopy' => true,
                'tl_class' => 'clr m12',
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

            'options' => [ 'parentTable', 'currentTable' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getChangeLanguageColumns' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getCatalogFields' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'useRedirect' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['useRedirect'],
            'inputType' => 'checkbox',

            'eval' => [

                'doNotCopy' => true,
                'tl_class' => 'clr m12',
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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getInternalCatalogFields' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getExternalCatalogFields' ],

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

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getPermissionTypes' ],

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog']['reference']['permissionType'],

            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ]
    ]
];