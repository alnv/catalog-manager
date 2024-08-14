<?php

use Contao\DC_Table;
use Alnv\CatalogManagerBundle\Classes\tl_catalog_form;

$GLOBALS['TL_DCA']['tl_catalog_form'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'ctable' => ['tl_catalog_form_fields'],
        'onload_callback' => [
            [tl_catalog_form::class, 'checkPermission'],
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
            'flag' => 1,
            'fields' => ['title'],
        ],
        'label' => [
            'showColumns' => true,
            'fields' => ['id', 'title', 'method']
        ],
        'operations' => [
            'editFields' => [
                'href' => 'table=tl_catalog_form_fields',
                'icon' => 'edit.svg'
            ],
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'header.svg'
            ],
            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.svg'
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg'
            ]
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ]
    ],
    'palettes' => [
        'default' => '{general_legend},title,jumpTo,method,anchor,resetForm,disableHtml5Validation,disableOnAutoItem;{submit_legend},disableSubmit,submitAttributes;{expert_legend:hide},template,attributes;{catalog_json_legend:hide},sendJsonHeader;'
    ],
    'fields' => [
        'id' => [
            'label' => ['ID', ''],
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'title' => [
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
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => [
                'tl_class' => 'clr',
                'fieldType' => 'radio',
            ],
            'relation' => [
                'type' => 'hasOne',
                'load' => 'eager'
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
                'tl_class' => 'w50'
            ],
            'options' => ['GET', 'POST'],
            'exclude' => true,
            'sql' => "varchar(8) NOT NULL default ''"
        ],
        'template' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_form']['template'],
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'maxlength' => 64,
                'tl_class' => 'w50',
                'includeBlankOption' => true
            ],
            'options_callback' => [tl_catalog_form::class, 'getFormTemplates'],
            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],
        'attributes' => [
            'inputType' => 'text',
            'eval' => [
                'size' => 2,
                'multiple' => true,
                'tl_class' => 'w50'
            ],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'submitAttributes' => [
            'inputType' => 'text',
            'eval' => [
                'size' => 2,
                'multiple' => true,
                'tl_class' => 'w50'
            ],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'sendJsonHeader' => [
            'inputType' => 'radio',
            'eval' => [
                'maxlength' => 16,
                'tl_class' => 'clr',
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options' => ['permanent', 'onAjaxCall'],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_form']['reference']['sendJsonHeader'],
            'exclude' => true,
            'sql' => "varchar(16) NOT NULL default ''"
        ],
        'disableOnAutoItem' => [
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50 m12'
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],
        'disableHtml5Validation' => [
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50 m12'
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],
        'anchor' => [
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 16,
                'tl_class' => 'w50'
            ],
            'exclude' => true,
            'sql' => "varchar(16) NOT NULL default ''"
        ]
    ]
];