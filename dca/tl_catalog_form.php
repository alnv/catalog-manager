<?php

$GLOBALS['TL_DCA']['tl_catalog_form'] = [

    'config' => [

        'dataContainer' => 'Table',
        'ctable' => [ 'tl_catalog_form_fields' ],

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
            'fields' => [ 'id' ]
        ],

        'operations' => [

        ]
    ],

    'palettes' => [

        'default' => ''
    ],

    'fields' => [

        'id' => [

            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],

        'tstamp' => [

            'sql' => "int(10) unsigned NOT NULL default '0'"
        ]
    ]
];