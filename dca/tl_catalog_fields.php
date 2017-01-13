<?php

$GLOBALS['TL_DCA']['tl_catalog_fields'] = [

    'config' => [

        'dataContainer' => 'Table',

        'ptable' => 'tl_catalog',

        'onsubmit_callback' => [

            [ 'CatalogManager\tl_catalog_fields', 'createFieldOnSubmit' ]
        ],

        'ondelete_callback' => [

            [ 'CatalogManager\tl_catalog_fields', 'dropFieldOnDelete' ]
        ],

        'sql' => [

            'keys' => [

                'id' => 'primary',
                'pid' => 'index'
            ]
        ]
    ],

    'list' => [

        'sorting' => [

            'mode' => 4,
            'fields' => [ 'sorting' ],
            'headerFields' => [ 'id', 'title', 'tablename' ],

            'child_record_callback' => [

                'CatalogManager\tl_catalog_fields',
                'getCatalogFieldList'
            ]
        ],

        'operations' => [

            'edit' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['edit'],
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

        '__selector__' => [ 'type' ],

        'default' => '{type_legend},type',

        'text' => '{type_legend},type,title,label,description,value,mandatory,tabindex,cssID;{field_settings_legend},rgxp,placeholder,readonly;{catalog_settings_legend},fieldname,statement,useIndex',

        'number' => '{type_legend},type,title,label,description,value,mandatory,tabindex,cssID;{field_settings_legend},min,max,step,rgxp,placeholder,readonly;{catalog_settings_legend},fieldname,statement,useIndex',

        'hidden' => '{type_legend},type,title,label,description,value,mandatory,tabindex,cssID;{field_settings_legend},rgxp,placeholder,readonly;{catalog_settings_legend},fieldname,statement,useIndex',

        'date' => '{type_legend},type,title,label,description,value,mandatory,tabindex,cssID;{field_settings_legend},tType,rgxp,placeholder,readonly;{catalog_settings_legend},fieldname,statement,useIndex',

        'textarea' => '{type_legend},type,title,label,description,value,mandatory,tabindex,cssID;{field_settings_legend},rte,cols,rows,placeholder,readonly;{catalog_settings_legend},fieldname,statement,useIndex',

        'select' => '{type_legend},type,title,label,description,value,mandatory,tabindex,cssID;{field_settings_legend},multiple,disabled;{catalog_settings_legend},fieldname,statement,useIndex',

        'radio' => '{type_legend},type,title,label,description,value,mandatory,tabindex,cssID;{field_settings_legend},disabled;{catalog_settings_legend},fieldname,statement,useIndex',

        'checkbox' => '{type_legend},type,title,label,description,value,mandatory,tabindex,cssID;{field_settings_legend},disabled;{catalog_settings_legend},fieldname,statement,useIndex',

        'upload' => '{type_legend},type,title,label,description,value,mandatory,tabindex,cssID;{field_settings_legend},extensions,maxsize;{catalog_settings_legend},fieldname,statement,useIndex',

        'message' => '{type_legend},type,title,label,description',

        'fieldsetStart' => '{type_legend},type,title,cssID',

        'fieldsetStop' => '{type_legend},type,title'
    ],

    'subpalettes' => [],

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

        'sorting' => [

            'sql' => "int(10) unsigned NOT NULL default '0'"
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

                'CatalogManager\tl_catalog_fields',
                'getFieldTypes'
            ],

            'default' => 'text',
            'exclude' => true,
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_fields'],
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'title' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['title'],
            'inputType' => 'text',

            'eval' => [

                'mandatory' => true,
                'tl_class' => 'w50',
                'maxlength' => 255
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'label' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['label'],
            'inputType' => 'text',

            'eval' => [

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
                'maxlength' => 2048
            ],

            'exclude' => true,
            'sql' => "varchar(2048) NOT NULL default ''"
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

        'fieldname' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['fieldname'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'mandatory' => true,
                'maxlength' => 64
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


        'readonly' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['readonly'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'clr'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
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

        'rte' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['rte'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'includeBlankOption'=>true,
                'tl_class' => 'w50'
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog_fields',
                'getRichTextEditor'
            ],

            'exclude' => true,
            'sql' => "varchar(16) NOT NULL default ''"
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

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'maxsize' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['maxsize'],
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
        ],

        'cssID' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['cssID'],
            'inputType' => 'text',

            'eval' => [

                'size' => 2,
                'multiple' => true,
                'tl_class' => 'w50',
                'maxlength' => 255
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'tabindex' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['tabindex'],
            'inputType' => 'text',

            'eval' => [

                'rgxp' => 'natural',
                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'mandatory' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['mandatory'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'rgxp' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['rgxp'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
                'includeBlankOption'=>true,
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog_fields',
                'getRGXPTypes'
            ],
            
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'statement' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['statement'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50',
                'maxlength' => 255,
                'mandatory' => true
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog_fields',
                'getSQLStatements'
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'useIndex' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['useIndex'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
                'includeBlankOption'=>true,
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog_fields',
                'getIndexes'
            ],

            'exclude' => true,
            'sql' => "varchar(10) NOT NULL default ''"
        ]
    ]
];