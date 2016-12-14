<?php

$GLOBALS['TL_DCA']['tl_catalog_fields'] = [

    'config' => [

        'dataContainer' => 'Table',

        'ptable' => 'tl_catalog',

        'sql' => [

            'keys' => [

                'id' => 'primary',
                'pid' => 'index'
            ]
        ]
    ],

    'list' => [],

    'palettes' => [],

    'fields' => [

        'id' => [

            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],

        'pid' => [

            'foreignKey' => 'tl_catalog.id',

            'relation' => array( 'type' => 'belongsTo', 'load' => 'eager' ),

            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],

        'tstamp' => [

            'sql' => "int(10) unsigned NOT NULL default '0'"
        ]
    ]
];