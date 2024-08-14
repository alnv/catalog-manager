<?php

use Alnv\CatalogManagerBundle\Backend\SupportPage;
use Alnv\CatalogManagerBundle\Modules\ModuleCatalogBookNavigation;
use Alnv\CatalogManagerBundle\Modules\ModuleUniversalView;
use Alnv\CatalogManagerBundle\Modules\ModuleMasterView;
use Alnv\CatalogManagerBundle\Modules\ModuleCatalogFilter;
use Alnv\CatalogManagerBundle\Elements\ContentCatalogEntity;
use Alnv\CatalogManagerBundle\Elements\ContentCatalogFilterForm;
use Alnv\CatalogManagerBundle\Elements\ContentSocialSharingButtons;
use Alnv\CatalogManagerBundle\Elements\ContentVisibilityPanelStop;
use Alnv\CatalogManagerBundle\Elements\ContentVisibilityPanelStart;
use Alnv\CatalogManagerBundle\CatalogDcAdapter;
use Alnv\CatalogManagerBundle\CatalogBreadcrumb;
use Alnv\CatalogManagerBundle\SearchIndexBuilder;
use Alnv\CatalogManagerBundle\CatalogContentElementParser;
use Alnv\CatalogManagerBundle\UserPermissionExtension;
use Alnv\CatalogManagerBundle\MemberPermissionExtension;
use Alnv\CatalogManagerBundle\BackendTemplateParser;
use Alnv\CatalogManagerBundle\CatalogManagerInitializer;
use Alnv\CatalogManagerBundle\CatalogWidgetAttributeParser;
use Alnv\CatalogManagerBundle\Widgets\CatalogMessageWidget;
use Alnv\CatalogManagerBundle\Widgets\CatalogTaxonomyWizard;
use Alnv\CatalogManagerBundle\Widgets\CatalogDuplexSelectWizard;
use Alnv\CatalogManagerBundle\Widgets\CatalogFilterFieldSelectWizard;
use Alnv\CatalogManagerBundle\Widgets\CatalogRelationRedirectWizard;
use Alnv\CatalogManagerBundle\Widgets\CatalogValueSetterWizard;
use Alnv\CatalogManagerBundle\Forms\CatalogMessageForm;
use Alnv\CatalogManagerBundle\Forms\CatalogFineUploaderForm;
use Alnv\CatalogManagerBundle\Inserttags\PseudoInsertTag;
use Alnv\CatalogManagerBundle\Inserttags\ActiveInsertTag;
use Alnv\CatalogManagerBundle\Inserttags\MasterInsertTag;
use Alnv\CatalogManagerBundle\Inserttags\CatalogInsertTag;
use Alnv\CatalogManagerBundle\Inserttags\TimestampInsertTag;
use Alnv\CatalogManagerBundle\Inserttags\FilterValuesInsertTag;
use Alnv\CatalogManagerBundle\Inserttags\RandomEntitiesIDInsertTag;
use Alnv\CatalogManagerBundle\CatalogParser;
use Contao\ArrayUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

const CATALOG_MANAGER_VERSION = "2.0.0";

ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 3, [
    'catalog-manager-extensions' => [
        'catalog-manager' => [
            'name' => 'catalog-manager',
            'icon' => 'system/modules/catalog-manager/assets/icons/icon.svg',
            'tables' => [
                'tl_catalog',
                'tl_catalog_fields'
            ]
        ],
        'filterform' => [
            'name' => 'filterform',
            'icon' => 'system/modules/catalog-manager/assets/icons/filterform.svg',
            'tables' => [
                'tl_catalog_form',
                'tl_catalog_form_fields'
            ]
        ],
        'support' => [
            'name' => 'support',
            'callback' => SupportPage::class,
            'stylesheet' => 'system/modules/catalog-manager/assets/support.css',
            'icon' => 'system/modules/catalog-manager/assets/icons/support-icon.svg'
        ]
    ]
]);

ArrayUtil::arrayInsert($GLOBALS['FE_MOD'], 3, [
    'catalog-manager' => [
        'catalogBookNavigation' => ModuleCatalogBookNavigation::class,
        'catalogUniversalView' => ModuleUniversalView::class,
        'catalogMasterView' => ModuleMasterView::class,
        'catalogFilter' => ModuleCatalogFilter::class,
    ]
]);

ArrayUtil::arrayInsert($GLOBALS['TL_CTE'], 3, [
    'catalog-manager' => [
        'catalogCatalogEntity' => ContentCatalogEntity::class,
        'catalogFilterForm' => ContentCatalogFilterForm::class,
        'catalogSocialSharingButtons' => ContentSocialSharingButtons::class,
        'catalogVisibilityPanelStart' => ContentVisibilityPanelStart::class,
        'catalogVisibilityPanelStop' => ContentVisibilityPanelStop::class
    ]
]);


if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
    $GLOBALS['TL_JAVASCRIPT']['catalogManagerBackendExtension'] = 'bundles/alnvcatalogmanager/BackendExtension.js';
    $GLOBALS['TL_HEAD']['catalogManagerBackendExtension'] = '<link href="bundles/alnvcatalogmanager/backend.css" rel="stylesheet">';
}

$GLOBALS['TL_HOOKS']['catalogManagerEntityOnCreate'] = [];
$GLOBALS['TL_HOOKS']['catalogManagerEntityOnUpdate'] = [];
$GLOBALS['TL_HOOKS']['catalogManagerEntityOnDelete'] = [];


$GLOBALS['TL_HOOKS']['loadDataContainer'][] = [CatalogDcAdapter::class, 'initialize'];
$GLOBALS['TL_HOOKS']['generateBreadcrumb'][] = [CatalogBreadcrumb::class, 'initialize'];
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = [SearchIndexBuilder::class, 'initialize'];
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = [UserPermissionExtension::class, 'initialize'];
$GLOBALS['TL_HOOKS']['initializeSystem'][] = [CatalogManagerInitializer::class, 'initialize'];
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = [MemberPermissionExtension::class, 'initialize'];
$GLOBALS['TL_HOOKS']['outputBackendTemplate'][] = [BackendTemplateParser::class, 'outputBackendTemplate'];
$GLOBALS['TL_HOOKS']['getContentElement'][] = [CatalogContentElementParser::class, 'parseVisibilityPanels'];
$GLOBALS['TL_HOOKS']['getAttributesFromDca'][] = [CatalogWidgetAttributeParser::class, 'parseCatalogNavigationAreasWidget'];

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [PseudoInsertTag::class, 'getInsertTagValue'];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ActiveInsertTag::class, 'getInsertTagValue'];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [MasterInsertTag::class, 'getInsertTagValue'];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [CatalogInsertTag::class, 'getInsertTagValue'];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [TimestampInsertTag::class, 'getInsertTagValue'];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [FilterValuesInsertTag::class, 'getInsertTagValue'];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [RandomEntitiesIDInsertTag::class, 'getInsertTagValue'];
$GLOBALS['TL_HOOKS']['getAllEvents'][] = [CatalogParser::class, 'getAllEvents'];

$GLOBALS['TL_CATALOG_MANAGER']['CORE_TABLES'] = [];
$GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'] = [];
$GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] = [];
$GLOBALS['TL_CATALOG_MANAGER']['VISIBILITY_PANEL'] = FALSE;

$GLOBALS['TL_CATALOG_MANAGER']['tinyMCE'] = ['tinyMCE', 'tinyFlash'];

$GLOBALS['TL_WRAPPERS']['stop'][] = 'fieldsetStop';
$GLOBALS['TL_WRAPPERS']['start'][] = 'fieldsetStart';
$GLOBALS['TL_WRAPPERS']['stop'][] = 'catalogVisibilityPanelStop';
$GLOBALS['TL_WRAPPERS']['start'][] = 'catalogVisibilityPanelStart';

$GLOBALS['TL_PERMISSIONS'][] = 'catalog';
$GLOBALS['TL_PERMISSIONS'][] = 'catalogp';
$GLOBALS['TL_PERMISSIONS'][] = 'filterform';
$GLOBALS['TL_PERMISSIONS'][] = 'filterformp';

$GLOBALS['BE_FFL']['catalogMessageWidget'] = CatalogMessageWidget::class;
$GLOBALS['BE_FFL']['catalogTaxonomyWizard'] = CatalogTaxonomyWizard::class;
$GLOBALS['BE_FFL']['catalogValueSetterWizard'] = CatalogValueSetterWizard::class;
$GLOBALS['BE_FFL']['catalogDuplexSelectWizard'] = CatalogDuplexSelectWizard::class;
$GLOBALS['BE_FFL']['catalogRelationRedirectWizard'] = CatalogRelationRedirectWizard::class;
$GLOBALS['BE_FFL']['catalogFilterFieldSelectWizard'] = CatalogFilterFieldSelectWizard::class;

$GLOBALS['TL_FFL']['catalogMessageForm'] = CatalogMessageForm::class;
$GLOBALS['TL_FFL']['catalogFineUploader'] = CatalogFineUploaderForm::class;

$GLOBALS['TL_CATALOG_MANAGER']['FIELD_TYPES'] = [
    'text' => ['dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField'],
    'date' => ['dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField'],
    'radio' => ['dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField'],
    'hidden' => ['dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField'],
    'number' => ['dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField'],
    'select' => ['dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField'],
    'message' => ['dcPicker' => 'message;', 'dcType' => 'dcPaletteField'],
    'map' => ['dcPicker' => 'description;', 'dcType' => 'dcPaletteField'],
    'upload' => ['dcPicker' => 'statement;', 'dcType' => 'dcPaletteField'],
    'checkbox' => ['dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField'],
    'textarea' => ['dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField'],
    'dbColumn' => ['dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField'],
    'fieldsetStart' => ['dcPicker' => 'isHidden;', 'dcType' => 'dcPaletteLegend'],
    'fieldsetStop' => []
];

$GLOBALS['TL_CATALOG_MANAGER']['FIELD_TYPE_CONVERTER'] = [
    'text' => 'text',
    'date' => 'text',
    'number' => 'text',
    'hidden' => 'text',
    'radio' => 'radio',
    'select' => 'select',
    'upload' => 'fileTree',
    'textarea' => 'textarea',
    'checkbox' => 'checkbox',
    'message' => 'catalogMessageWidget'
];

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['catalog_manager'] = [
    'ctlg_entity_status_insert' => [
        'recipients' => ['admin_email', 'raw_*', 'clean_*'],
        'email_replyTo' => ['admin_email', 'raw_*', 'clean_*'],
        'email_sender_name' => ['admin_email', 'raw_*', 'clean_*'],
        'email_recipient_cc' => ['admin_email', 'raw_*', 'clean_*'],
        'email_recipient_bcc' => ['admin_email', 'raw_*', 'clean_*'],
        'email_sender_address' => ['admin_email', 'raw_*', 'clean_*'],
        'email_subject' => ['admin_email', 'domain', 'raw_*', 'clean_*'],
        'attachment_tokens' => ['raw_*', 'clean_*', 'field_*', 'table_*'],
        'file_name' => ['admin_email', 'raw_*', 'clean_*', 'field_*', 'table_*'],
        'file_content' => ['admin_email', 'raw_*', 'clean_*', 'field_*', 'table_*'],
        'email_text' => ['admin_email', 'domain', 'raw_*', 'clean_*', 'field_*', 'table_*'],
        'email_html' => ['admin_email', 'domain', 'raw_*', 'clean_*', 'field_*', 'table_*']
    ],
    'ctlg_entity_status_duplicate' => [
        'recipients' => ['admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'email_replyTo' => ['admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'email_sender_name' => ['admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'email_recipient_cc' => ['admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'email_recipient_bcc' => ['admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'email_sender_address' => ['admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'email_subject' => ['admin_email', 'domain', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'attachment_tokens' => ['raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*'],
        'file_name' => ['admin_email', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*'],
        'file_content' => ['admin_email', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*'],
        'email_text' => ['admin_email', 'domain', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*'],
        'email_html' => ['admin_email', 'domain', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*']
    ],
    'ctlg_entity_status_update' => [
        'recipients' => ['admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'email_replyTo' => ['admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'email_sender_name' => ['admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'email_recipient_cc' => ['admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'email_recipient_bcc' => ['admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'email_sender_address' => ['admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'email_subject' => ['admin_email', 'domain', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*'],
        'attachment_tokens' => ['raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*'],
        'file_name' => ['admin_email', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*'],
        'file_content' => ['admin_email', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*'],
        'email_text' => ['admin_email', 'domain', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*'],
        'email_html' => ['admin_email', 'domain', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*']
    ],
    'ctlg_entity_status_delete' => [
        'recipients' => ['admin_email', 'rawOld_*', 'cleanOld_*'],
        'email_replyTo' => ['admin_email', 'rawOld_*', 'cleanOld_*'],
        'email_sender_name' => ['admin_email', 'rawOld_*', 'cleanOld_*'],
        'email_recipient_cc' => ['admin_email', 'rawOld_*', 'cleanOld_*'],
        'email_recipient_bcc' => ['admin_email', 'rawOld_*', 'cleanOld_*'],
        'email_sender_address' => ['admin_email', 'rawOld_*', 'cleanOld_*'],
        'email_subject' => ['admin_email', 'domain', 'rawOld_*', 'cleanOld_*'],
        'attachment_tokens' => ['field_*', 'table_*', 'rawOld_*', 'cleanOld_*'],
        'file_name' => ['admin_email', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*'],
        'file_content' => ['admin_email', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*'],
        'email_text' => ['admin_email', 'domain', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*'],
        'email_html' => ['admin_email', 'domain', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*'],
    ]
];

$GLOBALS['CM_ICON_SET'] = [
    'new' => 'bundles/alnvcatalogmanager/icons/new.svg',
    'pdf' => 'bundles/alnvcatalogmanager/assets/icons/pdf.svg',
    'edit' => 'bundles/alnvcatalogmanager/icons/edit.svg',
    'copy' => 'bundles/alnvcatalogmanager/icons/copy.svg',
    'delete' => 'bundles/alnvcatalogmanager/icons/delete.svg'
];