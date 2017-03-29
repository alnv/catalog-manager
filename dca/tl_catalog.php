<?php

$GLOBALS['TL_DCA']['tl_catalog'] = [

    'config' => [

        'dataContainer' => 'Table',

        'ctable' => [ 'tl_catalog_fields' ],

        'onload_callback' => [

            [ 'CatalogManager\tl_catalog', 'checkPermission' ],
            [ 'CatalogManager\tl_catalog', 'checkLicence' ]
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

            'mode' => 1,
            'flag' => 2,
            'fields' => [ 'name' ]
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

            'delete' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],

            'show' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            ]
        ]
    ],

    'palettes' => [

        '__selector__' => [ 'isBackendModule', 'useGeoCoordinates', 'addressInputType', 'useChangeLanguage', 'languageEntitySource', 'useRedirect' ],
        'default' => '{table_settings},tablename,cTables,pTable,addContentElements;{description_settings},name,info,description;{sorting_settings},mode,flag,headerFields,fields,showColumns,format;{navigation_legend},isBackendModule;{operations_legend},operations;{panel_layout_legend},panelLayout;{redirect_legend:hide},useRedirect;{geoCoordinates_legend:hide},useGeoCoordinates;{changeLanguageModule_legend:hide},useChangeLanguage'
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
                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'description' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['description'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'long clr',
                'maxlength' => 512
            ],

            'exclude' => true,
            'sql' => "varchar(512) NOT NULL default ''"
        ],

        'info' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['info'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'maxlength' => 16
            ],

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

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'isBackendModule' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['isBackendModule'],
            'inputType' => 'checkbox',

            'eval' => [

                'submitOnChange' => true,
            ],

            'save_callback' => [ [ 'CatalogManager\tl_catalog', 'checkModeTypeForPTableAndModes' ] ],
            
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
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'multiple' => true,
                'tl_class' => 'w50',
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getParentDataContainerFields' ],

            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'fields' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['fields'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'multiple' => true,
                'tl_class' => 'w50',
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog', 'getDataContainerFields' ],

            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'showColumns' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['showColumns'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12'
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
                'tl_class' => 'w50'
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
                'tl_class' => 'w50'
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
        ]
    ]
];