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
            'fields' => [ 'id', 'title', 'method' ]
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

            'copy' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif'
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

        'default' => '{general_legend},title,jumpTo,method,resetForm,disableSubmit;{expert_legend:hide},attributes;{catalog_json_legend:hide},sendJsonHeader;'
    ],

    'fields' => [

        'id' => [

            'label' => [ 'ID', '' ],
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
                'doNotCopy' => true,
                'tl_class' => 'w50',
                'mandatory' => true
            ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'jumpTo' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['jumpTo'],
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',

            'eval' => [

                'tl_class' => 'clr',
                'fieldType'=>'radio',
            ],

            'relation' => [

                'type'=>'hasOne',
                'load'=>'eager'
            ],

            'exclude' => true,
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],

        'resetForm' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['resetForm'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'disableSubmit' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['disableSubmit'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'w50 m12'
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'method' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['method'],
            'inputType' => 'select',
            'default' => 'GET',

            'eval' => [

                'maxlength' => 8,
                'tl_class'=>'w50'
            ],

            'options' => [ 'GET', 'POST' ],

            'exclude' => true,
            'sql' => "varchar(8) NOT NULL default ''"
        ],

        'attributes' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['attributes'],
            'inputType' => 'text',

            'eval' => [

                'size'=>2,
                'multiple'=>true,
                'tl_class'=>'w50'
            ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'sendJsonHeader' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['sendJsonHeader'],
            'inputType' => 'radio',

            'eval' => [

                'maxlength' => 16,
                'tl_class' => 'clr',
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options' => [ 'permanent', 'onAjaxCall' ],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_form']['reference']['sendJsonHeader'],

            'exclude' => true,
            'sql' => "varchar(16) NOT NULL default ''"
        ]
    ]
];