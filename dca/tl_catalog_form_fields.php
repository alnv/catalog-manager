<?php

$GLOBALS['TL_DCA']['tl_catalog_form_fields'] = [

    'config' => [

        'dataContainer' => 'Table',
        'ptable' => 'tl_catalog_form',

        'onload_callback' => [

            [ 'CatalogManager\tl_catalog_form_fields', 'checkPermission' ]
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
            'headerFields' => [ 'id' ],

            'child_record_callback' => [

                'CatalogManager\tl_catalog_form_fields',
                'setBackendRow'
            ]
        ],

        'operations' => [

            'edit' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['edit'],
                'href' => 'act=edit',
                'icon' => 'header.gif'
            ],

            'delete' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],

            'toggle' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['toggle'],
                'icon' => 'visible.gif',
                'href' => sprintf( 'catalogTable=%s', 'tl_catalog_form_fields' ),
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s, '. sprintf( "'%s'", 'tl_catalog_form_fields' ) .' )"',
                'button_callback' => [ 'CatalogManager\DCACallbacks', 'toggleIcon' ]
            ],

            'show' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            ]
        ]
    ],

    'palettes' => [

        '__selector__' => [ 'type', 'optionsType' ],

        'default' => '{field_type_legend},type,name,title;',
        'text' => '{field_type_legend},type,name,title;{general_legend},label,placeholder,description,defaultValue,tabindex,cssID;{template_legend:hide},template;{invisible_legend},invisible;',
        'radio' => '{field_type_legend},type,name,title;{general_legend},label,description,defaultValue,tabindex,cssID;{template_legend:hide},template;{option_legend},optionsType;{invisible_legend},invisible;',
        'select' => '{field_type_legend},type,name,title;{general_legend},label,description,defaultValue,tabindex,multiple,cssID;{template_legend:hide},template;{option_legend},optionsType;{invisible_legend},invisible;',
        'checkbox' => '{field_type_legend},type,name,title;{general_legend},label,description,defaultValue,tabindex,cssID;{template_legend:hide},template;{option_legend},optionsType;{invisible_legend},invisible;',
        'range' => '{field_type_legend},type,name,title;{general_legend},rangeLowLabel,rangeGreatLabel,description,tabindex,cssID;rangeLowType,rangeGreatType;{template_legend:hide},template;{invisible_legend},invisible;',
    ],

    'subpalettes' => [

        'optionsType_useOptions' => 'options',
        'optionsType_useColumn' => 'catalogColumn',
        'optionsType_useDbOptions' => 'dbTable,dbTableKey,dbTableValue,dbTaxonomy',
    ],

    'fields' => [

        'id' => [

            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],

        'pid' => [

            'foreignKey' => 'tl_catalog_form.id',

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

            'label' =>  &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['type'],
            'inputType' => 'select',
            'default' => 'text',

            'eval' => [

                'chosen' => true,
                'mandatory' => true,
                'tl_class' => 'w50',
                'submitOnChange' => true
            ],

            'options_callback' => [

                'CatalogManager\tl_catalog_form_fields',
                'getFilterFormFields'
            ],

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['reference']['type'],

            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'name' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['name'],
            'inputType' => 'text',

            'eval' => [

                'rgxp' => 'extnd',
                'maxlength' => 64,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
                'spaceToUnderscore' => true,
            ],

            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'title' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['title'],
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

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['label'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'maxlength' => 255
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'description' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['description'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'clr long',
                'maxlength' => 512
            ],

            'exclude' => true,
            'sql' => "varchar(512) NOT NULL default ''"
        ],

        'placeholder' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['placeholder'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'maxlength' => 255
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'multiple' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['multiple'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'rangeGreatLabel' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['rangeGreatLabel'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'maxlength' => 255
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'rangeLowLabel' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['rangeLowLabel'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'maxlength' => 255
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'rangeGreatType' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['rangeGreatType'],
            'inputType' => 'radio',

            'eval' => [

                'tl_class' => 'w50',
                'maxlength' => 12
            ],

            'options' => [ 'gt', 'gte' ],

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['reference']['rangeGreatType'],

            'exclude' => true,
            'sql' => "varchar(12) NOT NULL default ''"
        ],

        'rangeLowType' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['rangeLowType'],
            'inputType' => 'radio',

            'eval' => [

                'tl_class' => 'w50',
                'maxlength' => 12
            ],

            'options' => [ 'lt', 'lte' ],

            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['reference']['rangeLowType'],

            'exclude' => true,
            'sql' => "varchar(12) NOT NULL default ''"
        ],

        'optionsType' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['optionsType'],
            'inputType' => 'radio',
            'default' => 'useColumn',

            'eval' => [

                'maxlength' => 12,
                'mandatory' => true,
                'tl_class' => 'clr',
                'submitOnChange' => true
            ],

            'options' => [ 'useColumn', 'useOptions', 'useDbOptions' ],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['reference']['optionsType'],

            'exclude' => true,
            'sql' => "varchar(12) NOT NULL default ''"
        ],

        'options' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['options'],
            'inputType' => 'keyValueWizard',
            'exclude' => true,

            'eval' => [

                'doNotCopy' => true,
                'mandatory' => true
            ],

            'sql' => "blob NULL"
        ],

        'catalogColumn' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['catalogColumn'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'mandatory' => true,
                'tl_class' => 'w50'
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_form_fields', 'getTableColumns' ],

            'exclude' => true,
            'sql' => "varchar(128 NOT NULL default ''"
        ],

        'dbTable' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dbTable'],
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

            'options_callback' => [ 'CatalogManager\tl_catalog_form_fields', 'getTables' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'dbTableKey' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dbTableKey'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_form_fields', 'getColumnsByDbTable' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'dbTableValue' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dbTableValue'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'mandatory' => true,
                'doNotCopy' => true,
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_form_fields', 'getColumnsByDbTable' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'dbTaxonomy' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dbTaxonomy'],
            'inputType' => 'catalogTaxonomyWizard',

            'eval' => [

                'tl_class' => 'clr',
                'dcTable' => 'tl_catalog_form_fields',
                'taxonomyTable' => [ 'CatalogManager\tl_catalog_form_fields', 'getTaxonomyTable' ],
                'taxonomyEntities' => [ 'CatalogManager\tl_catalog_form_fields', 'getTaxonomyFields' ]
            ],

            'exclude' => true,
            'sql' => "blob NULL"
        ],

        'defaultValue' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['defaultValue'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50',
                'maxlength' => 255
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'cssID' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['cssID'],
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

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['tabindex'],
            'inputType' => 'text',

            'eval' => [

                'rgxp' => 'natural',
                'tl_class' => 'w50'
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'template' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['template'],
            'inputType' => 'select',

            'eval' => [

                'chosen'=> true,
                'tl_class'=> 'w50',
                'maxlength' => 64,
                'includeBlankOption'=> true
            ],

            'options_callback' => [ 'CatalogManager\tl_catalog_form_fields', 'getFieldTemplates' ],

            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'invisible' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form_fields']['invisible'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ]
    ]
];