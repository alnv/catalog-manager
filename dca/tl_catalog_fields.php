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

    'list' => [

        'sorting' => [

            'mode' => 0
        ],

        'label' => [

            'fields' => [ 'name' ],
        ],

        'operations' => [

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

        '__selector__' => [ 'type' ],

        'default' => '{type_legend},type',

        'text' => '{type_legend},type,name,columnName,description;{field_settings},inputType,required,placeholder,alt,value,maxlength,readOnly;',
        'textarea' => '{type_legend},type,name,columnName,description;{field_settings},required,placeholder,value,rows,cols,readOnly;',
        'select' => '{type_legend},type,name,columnName,description;{field_settings},required,multiple',
        'radio' => '{type_legend},type,name,columnName,description;{field_settings},required',
        'checkbox' => '{type_legend},type,name,columnName,description;{field_settings},required',
        'upload' => '{type_legend},type,name,columnName,description;{field_settings},required,multiple,extension',
        'fieldset' => '{type_legend},type,name,columnName,description;{field_settings}',
        'message' => '{type_legend},type,name,columnName,description;{field_settings}'
    ],

    'subpalettes' => [


    ],

    'fields' => [

        'id' => [

            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],

        'pid' => [

            'foreignKey' => 'tl_catalog.id',

            'relation' => [

                'type' => 'belongsTo',
                'load' => 'eager'
            ],

            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],

        'tstamp' => [

            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],

        'type' => [

            'label' =>  &$GLOBALS['TL_LANG']['tl_catalog_fields']['type'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'submitOnChange' => true
            ],

            'options_callback' => [

                'OceanCatalog\tl_catalog_fields',
                'getFieldTypes'
            ],

            'default' => 'text',
            'exclude' => true,
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_fields'],
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'name' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['name'],
            'inputType' => 'text',

            'eval' => [

                'mandatory' => true,
                'tl_class' => 'w50',
                'maxlength' => 255
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'description' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['description'],
            'inputType' => 'textarea',

            'eval' => [

                'tl_class' => 'clr',
                'maxlength' => 1024
            ],

            'exclude' => true,
            'sql' => "varchar(1024) NOT NULL default ''"
        ],

        'placeholder' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['placeholder'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'maxlength' => 255
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'columnName' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['columnName'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'mandatory' => true,
                'maxlength' => 64,
                'unique' => true
            ],

            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'inputType' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['inputType'],
            'inputType' => 'select',

            'eval' => [

                'tl_class' => 'w50',
                'mandatory' => true,
                'maxlength' => 64
            ],

            'options_callback' => [

                'OceanCatalog\tl_catalog_fields',
                'getInputTypes'
            ],

            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'step' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['step'],
            'inputType' => 'text',

            'eval' => [

                'rgxp' => 'natural',
                'tl_class' => 'w50'
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'required' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['required'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'm12 w50'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'readOnly' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['readOnly'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'm12 w50'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'alt' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['alt'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'maxlength' => 255
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'rows' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['rows'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'rgxp' => 'natural'
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'cols' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['cols'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'rgxp' => 'natural'
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'disabled' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['disabled'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'm12 w50'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'multiple' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['multiple'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'm12 w50'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'min' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['min'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'rgxp' => 'digit'
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'max' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['max'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'rgxp' => 'natural'
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'extensions' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['extensions'],
            'inputType' => 'text',

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'maxlength' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['maxlength'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'rgxp' => 'natural'
            ],

            'exclude' => true,
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],

        'value' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['value'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'maxlength' => 255
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ]
    ]
];