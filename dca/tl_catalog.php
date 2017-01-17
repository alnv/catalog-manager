<?php

$GLOBALS['TL_DCA']['tl_catalog'] = [

    'config' => [

        'dataContainer' => 'Table',

        'ctable' => [ 'tl_catalog_fields' ],
        
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

            'mode' => 0
        ],

        'label' => [

            'fields' => [ 'name' ],
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

        '__selector__' => [ 'isBackendModule' ],
        'default' => '{table_settings},name,tablename,description;{sorting_settings},mode,flag,cTables,pTable;{label_settings},showColumns,fields,headerFields,format;{panel_layout_legend},panelLayout;{navigation_settings},isBackendModule;'
    ],

    'subpalettes' => [

        'isBackendModule' => 'navArea,navPosition'
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

        'tablename' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['tablename'],
            'inputType' => 'text',

            'eval' => [

                'maxlength' => 128,
                'tl_class' => 'w50',
                'mandatory' => true
            ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'isBackendModule' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['isBackendModule'],
            'inputType' => 'checkbox',

            'eval' => [

                'submitOnChange' => true,
            ],

            'save_callback' => [

                [ 'CatalogManager\tl_catalog', 'checkModeTypeForPTableAndModes' ]
            ],
            
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

            'options_callback' => [

                'CatalogManager\tl_catalog',
                'getModeTypes'
            ],

            'save_callback' => [

                [ 'CatalogManager\tl_catalog', 'checkModeTypeRequirements' ]
            ],

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

            'options_callback' => [

                'CatalogManager\tl_catalog',
                'getAllTables'
            ],

            'save_callback' => [

                [ 'CatalogManager\tl_catalog', 'checkModeTypeForBackendModule' ]
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'cTables' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['cTables'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'multiple' => true,
                'tl_class' => 'w50',
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog',
                'getAllTables'
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'flag' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['flag'],
            'inputType' => 'select',
            'default' => '2',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50'
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog',
                'getFlagTypes'
            ],

            'exclude' => true,
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog']['flag'],
            'sql' => "varchar(2) NOT NULL default ''"
        ],

        'format' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog']['format'],
            'inputType' => 'text',

            'eval' => [

                'maxlength' => 128,
                'tl_class' => 'long',
                'allowHtml' => true
            ],

            'save_callback' => [

                [ 'CatalogManager\tl_catalog', 'checkModeTypeForFormat' ]
            ],

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

            'options_callback' => [

                'CatalogManager\tl_catalog',
                'getParentDataContainerFields'
            ],

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

            'options_callback' => [

                'CatalogManager\tl_catalog',
                'getDataContainerFields'
            ],

            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'showColumns' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['showColumns'],
            'inputType' => 'checkbox',

            'eval' => [

                //
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],


        'panelLayout' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['panelLayout'],
            'inputType' => 'checkbox',

            'eval' => [

                'multiple' => true
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog',
                'getPanelLayouts'
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'navArea' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['navArea'],
            'inputType' => 'select',
            'default' => 'system',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50'
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog',
                'getNavigationAreas'
            ],

            'exclude' => true,
            'sql' => "char(32) NOT NULL default ''"
        ],

        'navPosition' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['navPosition'],
            'inputType' => 'select',
            'default' => '0',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50'
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog',
                'getNavigationPosition'
            ],

            'exclude' => true,
            'sql' => "char(2) NOT NULL default ''"
        ]
    ]
];