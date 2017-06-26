<?php

$GLOBALS['TL_DCA']['tl_catalog_fields'] = [

    'config' => [

        'dataContainer' => 'Table',
        'ptable' => 'tl_catalog',

        'onload_callback' => [

            [ 'CatalogManager\tl_catalog_fields', 'checkPermission' ],
            [ 'CatalogManager\tl_catalog_fields', 'setOrderField' ]
        ],

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

            'toggle' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['toggle'],
                'icon' => 'visible.gif',
                'href' => sprintf( 'catalogTable=%s', 'tl_catalog_fields' ),
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s, '. sprintf( "'%s'", 'tl_catalog_fields' ) .' )"',
                'button_callback' => [ 'CatalogManager\DCACallbacks',  'toggleIcon' ]
            ],

            'show' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            ]
        ]
    ],

    'palettes' => [

        '__selector__' => [ 'type', 'optionsType', 'fileType', 'addMapInfoBox', 'useSize', 'usePreviewImage' ],

        'default' => '{general_legend},type',
        'text' => '{general_legend},type,title,label,description,value,placeholder,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy,unique,spaceToUnderscore,allowHtml,nospace,readonly,pagePicker,trailingSlash,doNotSaveEmpty,minlength,maxlength,rgxp,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag,charLength;{invisible_legend},invisible',
        'number' => '{general_legend},type,title,label,description,value,placeholder,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy,unique,readonly,doNotSaveEmpty,minval,maxval,rgxp,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag,charLength;{invisible_legend},invisible',
        'hidden' => '{general_legend},type,title,label,description,value,placeholder,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy,unique,doNotSaveEmpty,tstampAsDefault,minlength,maxlength,rgxp;{panelLayout_legend},exclude,filter,search,sort,flag,charLength;{invisible_legend},invisible',
        'date' => '{general_legend},type,tType,title,label,description,value,placeholder,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy,unique,readonly,doNotSaveEmpty,tstampAsDefault,rgxp,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag,charLength;{invisible_legend},invisible',
        'textarea' => '{general_legend},type,title,label,description,value,placeholder,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy,spaceToUnderscore,allowHtml,nospace,doNotSaveEmpty,readonly,rte,cols,rows,minlength,maxlength,rgxp,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag;{invisible_legend},invisible',
        'select' => '{general_legend},type,title,label,description,value,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{options_legend},optionsType;{evaluation_legend},mandatory,doNotCopy,multiple,chosen,submitOnChange,disabled,includeBlankOption,blankOptionLabel,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag,charLength;{invisible_legend},invisible',
        'radio' => '{general_legend},type,title,label,description,value,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{options_legend},optionsType;{evaluation_legend},mandatory,doNotCopy,disabled,submitOnChange,includeBlankOption,blankOptionLabel,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag,charLength;{invisible_legend},invisible',
        'checkbox' => '{general_legend},type,title,label,description,value,tabindex,cssID;{database_legend},fieldname,statement,useIndex;{options_legend},optionsType;{evaluation_legend},mandatory,doNotCopy,multiple,enableToggleIcon,disabled,submitOnChange,tl_class;{panelLayout_legend},exclude,filter,search,sort,flag,charLength;{invisible_legend},invisible',
        'upload' => '{general_legend},type,title,label,description,value,tabindex,cssID;{database_legend},fieldname,statement;{file_type_legend},fileType;{evaluation_legend},mandatory,doNotCopy,disabled,filesOnly,extensions,path,maxsize,tl_class;{panelLayout_legend},exclude;{invisible_legend},invisible',
        'message' => '{general_legend},type,fieldname,title,message;{invisible_legend},invisible',
        'map' => '{general_legend},type,fieldname,title,label,description;{mapField_legend},latField,lngField,mapTemplate,mapZoom,mapType,mapScrollWheel,mapMarker,addMapInfoBox,mapStyle;{invisible_legend},invisible',
        'fieldsetStart' => '{general_legend},type,title,label;{invisible_legend},invisible',
        'fieldsetStop' => '{general_legend},type,title;{invisible_legend},invisible',
        'dbColumn' => '{general_legend},type,title,label,description;{database_legend},fieldname,statement,useIndex;{evaluation_legend},mandatory,doNotCopy;{invisible_legend},invisible',
    ],

    'subpalettes' => [

        'useSize' => 'size',
        'addMapInfoBox' => 'mapInfoBoxContent',
        'usePreviewImage' => 'imageTemplate,previewImagePosition',

        'optionsType_useOptions' => 'options',
        'optionsType_useForeignKey' => 'dbTable,dbTableKey,addRelationWizard',
        'optionsType_useDbOptions' => 'dbTable,dbTableKey,dbTableValue,dbTaxonomy,addRelationWizard',

        'fileType_file' => 'fileTemplate,fileTitle,fileText',
        'fileType_files' => 'filesTemplate,sortBy,orderField,metaIgnore',
        'fileType_image' => 'imageTemplate,imageTitle,imageAlt,imageURL,imageCaption,fullsize,useSize',
        'fileType_gallery' => 'galleryTemplate,sortBy,orderField,perRow,perPage,numberOfItems,fullsize,metaIgnore,useSize,useArrayFormat,usePreviewImage'
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

        'sorting' => [

            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],

        'tstamp' => [

            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],

        'type' => [

            'label' =>  &$GLOBALS['TL_LANG']['tl_catalog_fields']['type'],
            'inputType' => 'select',
            'default' => 'text',

            'eval' => [

                'chosen' => true,
                'submitOnChange' => true
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog_fields',
                'getFieldTypes'
            ],

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['reference']['type'],

            'exclude' => true,
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

                'rgxp' => 'extnd',
                'maxlength' => 64,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
                'spaceToUnderscore' => true,
            ],

            'save_callback' => [

                [ 'CatalogManager\tl_catalog_fields', 'fieldnameBlackList' ],
                [ 'CatalogManager\tl_catalog_fields', 'checkFieldname' ],
                [ 'CatalogManager\tl_catalog_fields', 'checkUniqueValue' ],
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
                'tl_class' => 'w50'
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
                'includeBlankOption'=> true
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog_fields',
                'getRGXPTypes'
            ],

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['reference']['rgxp'],

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

                'maxlength' => 64,
                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
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

        'submitOnChange' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['submitOnChange'],
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

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['reference']['tl_class'],

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

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['reference']['flag'],

            'exclude' => true,
            'sql' => "varchar(2) NOT NULL default ''"
        ],

        'charLength' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['charLength'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50'
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'tstampAsDefault' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['tstampAsDefault'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'invisible' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['invisible'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],


        'pagePicker' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['pagePicker'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'fileType' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['fileType'],
            'inputType' => 'radio',

            'eval' => [

                'tl_class' => 'clr',
                'submitOnChange' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption'=>true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getFilesTypes' ],

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['reference']['fileType'],
            
            'sql' => "varchar(256) NOT NULL default ''"
        ],
        
        'useSize' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['useSize'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12',
                'submitOnChange' => true,
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'size' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['size'],
            'inputType' => 'imageSize',
            'options' => \System::getImageSizes(),

            'eval' => [

                'nospace'=>true,
                'rgxp'=>'natural',
                'maxlength' => 64,
                'helpwizard'=>true,
                'tl_class' => 'w50 m12',
                'submitOnChange' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption'=>true
            ],

            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'fullsize' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['fullsize'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'imageTitle' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['imageTitle'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'blankOptionLabel' => '-',
                'includeBlankOption'=>true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getTextFieldsByParentID' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'imageAlt' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['imageAlt'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'blankOptionLabel' => '-',
                'includeBlankOption'=>true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getTextFieldsByParentID' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'imageCaption' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['imageCaption'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'blankOptionLabel' => '-',
                'includeBlankOption'=>true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getTextFieldsByParentID' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'imageURL' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['imageURL'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'blankOptionLabel' => '-',
                'includeBlankOption'=>true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getTextFieldsByParentID' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'fileTitle' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['fileTitle'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'blankOptionLabel' => '-',
                'includeBlankOption'=>true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getTextFieldsByParentID' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'fileText' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['fileText'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'blankOptionLabel' => '-',
                'includeBlankOption'=>true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getTextFieldsByParentID' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'optionsType' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['optionsType'],
            'inputType' => 'radio',

            'eval' => [

                'maxlength' => 16,
                'tl_class' => 'clr',
                'submitOnChange' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption'=>true,
            ],

            'options' => [

                'useOptions',
                'useDbOptions',
                'useForeignKey'
            ],

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['reference']['optionsType'],

            'exclude' => true,
            'sql' => "varchar(16) NOT NULL default ''"
        ],

        'options' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['options'],
            'inputType' => 'keyValueWizard',
            'exclude' => true,

            'eval' => [

                'doNotCopy' => true,
                'mandatory' => true
            ],

            'sql' => "blob NULL"
        ],

        'dbTable' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['dbTable'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
                'submitOnChange' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption'=>true,
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getTables' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'dbTableKey' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['dbTableKey'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getColumnsByDbTable' ],
            
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'dbTableValue' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['dbTableValue'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getColumnsByDbTable' ],
            
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'dbTaxonomy' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['dbTaxonomy'],
            'inputType' => 'catalogTaxonomyWizard',

            'eval' => [

                'tl_class' => 'clr',
                'dcTable' => 'tl_catalog_fields',
                'taxonomyTable' => [ 'CatalogManager\tl_catalog_fields', 'getTaxonomyTable' ],
                'taxonomyEntities' => [ 'CatalogManager\tl_catalog_fields', 'getTaxonomyFields' ]
            ],

            'exclude' => true,
            'sql' => "blob NULL"
        ],

        'latField' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['latField'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getCatalogFieldsByParentID' ],

            'exclude' => true,
            'sql' => "char(128) NOT NULL default ''"
        ],

        'lngField' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['lngField'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getCatalogFieldsByParentID' ],

            'exclude' => true,
            'sql' => "char(128) NOT NULL default ''"
        ],

        'mapTemplate' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['mapTemplate'],
            'inputType' => 'select',
            'default' => 'map_catalog_default',

            'eval' => [

                'chosen' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
                'mandatory' => true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getMapTemplates' ],
            
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'mapZoom'=> [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['mapZoom'],
            'inputType' => 'select',
            'default' => 10,

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50'
            ],

            'options' => [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20 ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'mapType' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['mapType'],
            'inputType' => 'select',
            'default' => 'HYBRID',

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50'
            ],

            'options' => [ 'ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN' ],

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['reference']['mapType'],

            'exclude' => true,
            'sql' => "varchar(16) NOT NULL default ''"
        ],

        'mapScrollWheel' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['mapScrollWheel'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'mapMarker' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['mapMarker'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'mapStyle' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['mapStyle'],
            'inputType' => 'textarea',

            'eval' => [

                'tl_class' => 'clr',
                'rte' => 'ace|html',
                'allowHtml' => true
            ],

            'exclude' => true,
            'sql' => "text NULL"
        ],

        'addMapInfoBox' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['addMapInfoBox'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12',
                'submitOnChange' => true
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'mapInfoBoxContent' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['mapInfoBoxContent'],
            'inputType' => 'textarea',

            'eval' => [

                'rte' => 'ace|html',
                'tl_class' => 'clr',
                'allowHtml' => true
            ],

            'exclude' => true,
            'sql' => "text NULL"
        ],

        'message' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['message'],
            'inputType' => 'textarea',

            'eval' => [

                'rte' => 'tinyMCE',
                'tl_class' => 'clr',
                'allowHtml' => true
            ],

            'exclude' => true,
            'sql' => "text NULL"
        ],

        'addRelationWizard' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['addRelationWizard'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'clr'
            ],

            'exclude' => true,
            'sql' => "text NULL"
        ],

        'enableToggleIcon' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['enableToggleIcon'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'imageTemplate' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['imageTemplate'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 255,
                'mandatory' => true,
                'tl_class' => 'w50'
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getImageTemplates' ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'galleryTemplate' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['galleryTemplate'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 255,
                'mandatory' => true,
                'tl_class' => 'w50'
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getGalleryTemplates' ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'sortBy' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['sortBy'],
            'inputType' => 'select',

            'options' => [

                'custom',
                'name_asc',
                'name_desc',
                'date_asc',
                'date_desc',
                'random'
            ],

            'eval' => [

                'chosen' => true,
                'maxlength' => 32,
                'tl_class' => 'w50',
                'submitOnChange' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['reference']['sortBy'],

            'exclude' => true,
            'sql' => "varchar(32) NOT NULL default ''"
        ],

        'orderField' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['orderField'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'doNotCopy' => true,
                'mandatory' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getOrderFields' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'metaIgnore' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['metaIgnore'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'perRow' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['perRow'],
            'inputType' => 'select',
            'default' => 4,

            'options' => [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ],

            'eval' => [

                'chosen' => true,
                'tl_class' => 'w50'
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'perPage' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['perPage'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'rgxp' => 'natural'
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'numberOfItems' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['numberOfItems'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'rgxp' => 'natural'
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'fileTemplate' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['fileTemplate'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 255,
                'mandatory' => true,
                'tl_class' => 'w50'
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getFileTemplates' ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'filesTemplate' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['filesTemplate'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 255,
                'mandatory' => true,
                'tl_class' => 'w50'
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_fields', 'getFilesTemplates' ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'usePreviewImage' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['usePreviewImage'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12',
                'submitOnChange' => true
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'previewImagePosition' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['previewImagePosition'],
            'inputType' => 'radio',
            'default' => 'first',

            'eval' => [

                'maxlength' => 16,
                'tl_class' => 'w50',
                'mandatory' => true,
            ],

            'options' => [ 'first', 'middle', 'last' ],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['reference']['previewImagePosition'],

            'exclude' => true,
            'sql' => "varchar(16) NOT NULL default ''"
        ],

        'useArrayFormat' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_fields']['useArrayFormat'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'm12 w50'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ]
    ]
];