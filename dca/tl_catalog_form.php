<?php

$GLOBALS['TL_DCA']['tl_catalog_form'] = [

    'config' => [

        'dataContainer' => 'Table',
        'ctable' => [ 'tl_catalog_form_fields' ],

        'onload_callback' => [

            [ 'CatalogManager\tl_catalog_form', 'checkPermission' ],
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
            'fields' => [ 'title' ],
        ],

        'label' => [

            'showColumns' => true,
            'fields' => [ 'title', 'catalog' ]
        ],

        'operations' => [

            'editFields' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['editFields'],
                'href' => 'table=tl_catalog_form_fields',
                'icon' => 'edit.gif'
            ],

            'edit' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['edit'],
                'href' => 'act=edit',
                'icon' => 'header.gif'
            ],

            'delete' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],

            'show' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            ]
        ]
    ],

    'palettes' => [

        'default' => 'title,catalog'
    ],

    'fields' => [

        'id' => [

            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],

        'tstamp' => [

            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],

        'title' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['title'],
            'inputType' => 'text',

            'eval' => [

                'maxlength' => 128,
                'tl_class' => 'w50',
                'mandatory' => true
            ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'catalog' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['catalog'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'mandatory' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_form', 'getCatalogs' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ]
    ]
];