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

        'default' => '{general_legend},type',
        'text' => '{general_legend},type,title,label,description,value,placeholder,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy,unique,spaceToUnderscore,allowHtml,nospace,readonly,trailingSlash,doNotSaveEmpty,minlength,maxlength,rgxp,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag',
        'number' => '{general_legend},type,title,label,description,value,placeholder,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy,unique,readonly,doNotSaveEmpty,minval,maxval,rgxp,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag',
        'hidden' => '{general_legend},type,title,label,description,value,placeholder,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy,unique,doNotSaveEmpty,tstampAsDefault,minlength,maxlength,rgxp;{panelLayout_legend},exclude,filter,search,sort,flag',
        'date' => '{general_legend},type,tType,title,label,description,value,placeholder,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy,unique,readonly,doNotSaveEmpty,tstampAsDefault,rgxp,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag',
        'textarea' => '{general_legend},type,title,label,description,value,placeholder,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy,spaceToUnderscore,allowHtml,nospace,doNotSaveEmpty,readonly,rte,cols,rows,minlength,maxlength,rgxp,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag',
        'select' => '{general_legend},type,title,label,description,value,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy,multiple,chosen,disabled,includeBlankOption,blankOptionLabel,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag',
        'radio' => '{general_legend},type,title,label,description,value,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy,disabled,includeBlankOption,blankOptionLabel,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag',
        'checkbox' => '{general_legend},type,title,label,description,value,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy,multiple,disabled,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag',
        'upload' => '{general_legend},type,title,label,description,value,tabindex,cssID;{database_legend},fieldname,statement;{evaluation_legend},mandatory,doNotCopy,multiple,disabled,filesOnly,extensions,path,maxsize,tl_class;{panelLayout_legend},exclude',
        'message' => '{general_legend},type,title,label,description',
        'fieldsetStart' => '{general_legend},type,title,cssID',
        'fieldsetStop' => '{general_legend},type,title'
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
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'clr long',
                'maxlength' => 512
            ],

            'exclude' => true,
            'sql' => "varchar(512) NOT NULL default ''"
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

            'save_callback' => [

                [ 'CatalogManager\tl_catalog_fields', 'renameFieldname' ]
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

                'tl_class' => 'w50',
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

                'tl_class' => 'w50'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'multiple' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['multiple'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'rte' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['rte'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50 m12',
                'blankOptionLabel' => '-',
                'includeBlankOption'=>true,
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog_fields',
                'getRichTextEditor'
            ],

            'exclude' => true,
            'sql' => "varchar(16) NOT NULL default ''"
        ],

        'extensions' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['extensions'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50 clr',
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'path' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['path'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'filesOnly' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['filesOnly'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
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

                'tl_class' => 'w50',
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
                'blankOptionLabel' => '-',
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
                'blankOptionLabel' => '-',
                'includeBlankOption'=>true,
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog_fields',
                'getIndexes'
            ],

            'exclude' => true,
            'sql' => "varchar(10) NOT NULL default ''"
        ],

        'maxlength' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['maxlength'],
            'inputType' => 'text',

            'eval' => [

                'maxlength' => 12,
                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "varchar(12) NOT NULL default ''"
        ],

        'minlength' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['minlength'],
            'inputType' => 'text',

            'eval' => [

                'maxlength' => 12,
                'tl_class' => 'w50 clr',
            ],

            'exclude' => true,
            'sql' => "varchar(12) NOT NULL default ''"
        ],

        'maxval' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['maxval'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'minval' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['minval'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50 clr',
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'nospace' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['nospace'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'allowHtml' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['allowHtml'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'doNotSaveEmpty' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['doNotSaveEmpty'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'spaceToUnderscore' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['spaceToUnderscore'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'unique' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['unique'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'trailingSlash' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['trailingSlash'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'includeBlankOption' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['includeBlankOption'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'blankOptionLabel' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['blankOptionLabel'],
            'inputType' => 'text',

            'eval' => [

                'maxlength' => 12,
                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "varchar(12) NOT NULL default ''"
        ],

        'chosen' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['chosen'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'doNotCopy' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['doNotCopy'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'tl_class' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['tl_class'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'multiple' => true,
                'tl_class' => 'w50',
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog_fields',
                'getTLClasses'
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'filter' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['filter'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'search' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['search'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'sort' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['sort'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'exclude' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['exclude'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'flag' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['flag'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50 clr',
                'blankOptionLabel' => '-',
                'includeBlankOption'=> true,
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog_fields',
                'getFieldFlags'
            ],

            'exclude' => true,
            'sql' => "varchar(2) NOT NULL default ''"
        ],

        'tstampAsDefault' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['timestampAsDefault'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ]
    ]
];