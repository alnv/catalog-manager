<?php

use Contao\Input;
use Alnv\CatalogManagerBundle\Classes\tl_content;
use Alnv\CatalogManagerBundle\DcCallbacks;

$GLOBALS['TL_DCA']['tl_content']['palettes']['catalogCatalogEntity'] = '{type_legend},type,headline;{entity_legend},catalogTablename,catalogEntityId,catalogEntityTemplate,catalogRedirectType;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['catalogFilterForm'] = '{type_legend},type,headline;{include_legend},catalogForm;{template_legend:hide},customCatalogElementTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['catalogSocialSharingButtons'] = '{type_legend},type,headline;{social_sharing_legend},catalogSocialSharingButtons,catalogSocialSharingTable,catalogSocialSharingTitle,catalogSocialSharingDescription,catalogSocialSharingTemplate,catalogDisableSocialSharingCSS;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['catalogVisibilityPanelStop'] = '{type_legend},type;{protected_legend:hide},protected;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['catalogVisibilityPanelStart'] = '{type_legend},type;{panel_settings},catalogNegateVisibility;{protected_legend:hide},protected;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'catalogRedirectType';

$GLOBALS['TL_DCA']['tl_content']['subpalettes']['catalogRedirectType_master'] = 'catalogRedirectPage,catalogRedirectText,catalogRedirectTitle';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['catalogRedirectType_internal'] = 'catalogRedirectPage,catalogRedirectText,catalogRedirectTitle';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['catalogRedirectType_link'] = 'catalogRedirectUrl,catalogRedirectTarget,catalogRedirectText,catalogRedirectTitle';

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogNegateVisibility'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogNegateVisibility'],
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr'
    ],
    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogForm'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogForm'],
    'inputType' => 'select',
    'eval' => [
        'chosen' => true,
        'mandatory' => true,
        'submitOnChange' => true,
        'blankOptionLabel' => '-',
        'tl_class' => 'w50 wizard',
        'includeBlankOption' => true,
    ],
    'wizard' => [
        [
            tl_content::class, 'editCatalogForm'
        ]
    ],
    'options_callback' => [tl_content::class, 'getCatalogForms'],
    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogSocialSharingTable'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogSocialSharingTable'],
    'inputType' => 'select',
    'eval' => [
        'chosen' => true,
        'maxlength' => 128,
        'mandatory' => true,
        'tl_class' => 'w50',
        'submitOnChange' => true,
        'includeBlankOption' => true
    ],
    'options_callback' => [tl_content::class, 'getCatalogTables'],
    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogSocialSharingTitle'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogSocialSharingTitle'],
    'inputType' => 'select',
    'eval' => [
        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
        'includeBlankOption' => true
    ],
    'options_callback' => [tl_content::class, 'getCatalogFields'],
    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogSocialSharingDescription'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogSocialSharingDescription'],
    'inputType' => 'select',
    'eval' => [
        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
        'includeBlankOption' => true
    ],
    'options_callback' => [tl_content::class, 'getCatalogFields'],
    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogSocialSharingButtons'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogSocialSharingButtons'],
    'inputType' => 'checkboxWizard',
    'eval' => [
        'multiple' => true,
        'tl_class' => 'clr',
    ],
    'reference' => &$GLOBALS['TL_LANG']['MSC']['sharingButtons'],
    'options_callback' => [tl_content::class, 'getSocialSharingButtons'],
    'exclude' => true,
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogSocialSharingTemplate'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogSocialSharingTemplate'],
    'inputType' => 'select',
    'eval' => [
        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50'
    ],
    'options_callback' => [tl_content::class, 'getSocialSharingTemplates'],
    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogDisableSocialSharingCSS'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogDisableSocialSharingCSS'],
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr'
    ],
    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['customCatalogElementTpl'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['customTpl'],
    'inputType' => 'select',
    'eval' => [
        'includeBlankOption' => true,
        'tl_class' => 'w50',
        'chosen' => true,
    ],
    'options_callback' => [tl_content::class, 'getFilterFormTemplates'],
    'exclude' => true,
    'sql' => "varchar(64) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogTablename'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogTablename'],
    'inputType' => 'select',
    'eval' => [
        'includeBlankOption' => true,
        'submitOnChange' => true,
        'mandatory' => true,
        'tl_class' => 'w50',
        'chosen' => true,
    ],
    'options_callback' => [tl_content::class, 'getTablenames'],
    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogEntityId'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogEntityId'],
    'inputType' => 'select',
    'eval' => [
        'includeBlankOption' => true,
        'mandatory' => true,
        'tl_class' => 'w50',
        'chosen' => true,
    ],
    'options_callback' => [tl_content::class, 'getCatalogEntities'],
    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogRedirectType'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogRedirectType'],
    'inputType' => 'radio',
    'eval' => [
        'includeBlankOption' => true,
        'submitOnChange' => true,
        'tl_class' => 'clr'
    ],
    'options' => ['master', 'internal', 'link'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['catalogRedirectType']['options'],
    'exclude' => true,
    'sql' => "varchar(10) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogRedirectPage'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogRedirectPage'],
    'inputType' => 'pageTree',
    'eval' => [
        'mandatory' => true,
        'fieldType' => 'radio'
    ],
    'foreignKey' => 'tl_page.title',
    'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogRedirectUrl'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogRedirectUrl'],
    'inputType' => 'text',
    'eval' => [
        'rgxp' => 'url',
        'tl_class' => 'w50',
        'maxlength' => 255,
        'mandatory' => true,
        'dcaPicker' => true,
        'decodeEntities' => true,
        'addWizardClass' => false
    ],
    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogRedirectTarget'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogRedirectTarget'],
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'w50 m12'
    ],
    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogRedirectTitle'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogRedirectTitle'],
    'inputType' => 'text',
    'eval' => [
        'tl_class' => 'w50'
    ],
    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogRedirectText'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogRedirectText'],
    'inputType' => 'text',
    'eval' => [
        'tl_class' => 'w50'
    ],
    'exclude' => true,
    'sql' => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogEntityTemplate'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogEntityTemplate'],
    'inputType' => 'select',
    'eval' => [
        'tl_class' => 'w50',
        'chosen' => true
    ],
    'options_callback' => [tl_content::class, 'getEntityTemplates'],
    'exclude' => true,
    'sql' => "varchar(128) NOT NULL default ''"
];

if (!isset($GLOBALS['TL_DCA']['tl_content']['edit'])) $GLOBALS['TL_DCA']['tl_content']['edit'] = [];
if (!isset($GLOBALS['TL_DCA']['tl_content']['edit']['buttons_callback'])) $GLOBALS['TL_DCA']['tl_content']['edit']['buttons_callback'] = [];

$GLOBALS['TL_DCA']['tl_content']['edit']['buttons_callback'][] = [DcCallbacks::class, 'removeDcFormOperations'];

if (Input::get('do') && is_array($GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'])) {

    $arrTablesWithContent = [];
    foreach ($GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] as $arrExtension) {

        if ($arrExtension['modulename'] == Input::get('do')) {
            if ($arrExtension['addContentElements']) {
                $arrTablesWithContent[] = $arrExtension['tablename'];
            }

            if (!empty($arrExtension['cTables'])) {
                foreach ($arrExtension['cTables'] as $strTable) {
                    if (isset($GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strTable]) && $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strTable]['addContentElements']) {
                        $arrTablesWithContent[] = $strTable;
                    }
                }
            }

            break;
        }
    }

    if (count($arrTablesWithContent) == 1) {
        $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = $arrTablesWithContent[0];
    }

    if (count($arrTablesWithContent) > 1) {
        $intIndex = array_search(Input::get('ctlg_table'), $arrTablesWithContent);
        if ($intIndex !== false) {
            $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = $arrTablesWithContent[$intIndex];
        }
    }
}