<?php

define("CATALOG_MANAGER_VERSION", "1.32.25");

if (!\Config::get('hideCatalogManager')) {
    array_insert( $GLOBALS['BE_MOD'], 3, [
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
                'callback' => CatalogManager\SupportPage::class,
                'stylesheet' => 'system/modules/catalog-manager/assets/support.css',
                'icon' => 'system/modules/catalog-manager/assets/icons/support-icon.svg'
            ],
        ]
    ]);
}

array_insert($GLOBALS['FE_MOD'], 3, [
    'catalog-manager' => [
        'catalogBookNavigation' => 'CatalogManager\ModuleCatalogBookNavigation',
        'catalogTaxonomyTree' => 'CatalogManager\ModuleCatalogTaxonomyTree',
        'catalogUniversalView' => 'CatalogManager\ModuleUniversalView',
        'catalogMasterView' => 'CatalogManager\ModuleMasterView',
        'catalogFilter' => 'CatalogManager\ModuleCatalogFilter',
    ]
]);

array_insert($GLOBALS['TL_CTE'], 3, [
    'catalog-manager' => [
        'catalogCatalogEntity' => 'CatalogManager\ContentCatalogEntity',
        'catalogFilterForm' => 'CatalogManager\ContentCatalogFilterForm',
        'catalogSocialSharingButtons' => 'CatalogManager\ContentSocialSharingButtons',
        'catalogVisibilityPanelStart' => 'CatalogManager\ContentVisibilityPanelStart',
        'catalogVisibilityPanelStop' => 'CatalogManager\ContentVisibilityPanelStop'
    ]
]);

if (TL_MODE == 'BE') {
    $GLOBALS['TL_JAVASCRIPT']['catalogManagerBackendExtension'] = $GLOBALS['TL_CONFIG']['debugMode']
        ? 'system/modules/catalog-manager/assets/BackendExtension.js'
        : 'system/modules/catalog-manager/assets/BackendExtension.js';
    $GLOBALS['TL_CSS']['catalogManagerBackendExtension'] = $GLOBALS['TL_CONFIG']['debugMode']
        ? 'system/modules/catalog-manager/assets/backend.css'
        : 'system/modules/catalog-manager/assets/backend.css';
}

$GLOBALS['TL_HOOKS']['catalogManagerEntityOnCreate'] = [];
$GLOBALS['TL_HOOKS']['catalogManagerEntityOnUpdate'] = [];
$GLOBALS['TL_HOOKS']['catalogManagerEntityOnDelete'] = [];

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ 'CatalogManager\PseudoInsertTag', 'getInsertTagValue' ];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ 'CatalogManager\ActiveInsertTag', 'getInsertTagValue' ];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ 'CatalogManager\MasterInsertTag', 'getInsertTagValue' ];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ 'CatalogManager\CatalogInsertTag', 'getInsertTagValue' ];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ 'CatalogManager\TimestampInsertTag', 'getInsertTagValue' ];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ 'CatalogManager\FilterValuesInsertTag', 'getInsertTagValue' ];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ 'CatalogManager\RandomEntitiesIDInsertTag', 'getInsertTagValue' ];
$GLOBALS['TL_HOOKS']['getAllEvents'][] = ['CatalogManager\CatalogParser', 'getAllEvents'];
if (version_compare('4.12', VERSION, '<=')) {
    if (\System::getContainer()->getParameter('contao.legacy_routing')) {
        $GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = ['CatalogManager\RoutingBuilder', 'initialize'];
    }
} else {
    $GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = ['CatalogManager\RoutingBuilder', 'initialize'];
}
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['CatalogManager\CatalogDcAdapter', 'initialize'];
$GLOBALS['TL_HOOKS']['generateBreadcrumb'][] = ['CatalogManager\CatalogBreadcrumb', 'initialize'];
$GLOBALS['TL_HOOKS']['sqlCompileCommands'][] = ['CatalogManager\SQLCompileCommands', 'initialize'];
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = ['CatalogManager\SearchIndexBuilder', 'initialize'];
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['CatalogManager\UserPermissionExtension', 'initialize'];
$GLOBALS['TL_HOOKS']['initializeSystem'][] = ['CatalogManager\CatalogManagerInitializer', 'initialize'];
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['CatalogManager\MemberPermissionExtension', 'initialize'];
$GLOBALS['TL_HOOKS']['outputBackendTemplate'][] = ['CatalogManager\BackendTemplateParser', 'outputBackendTemplate'];
$GLOBALS['TL_HOOKS']['getContentElement'][] = ['CatalogManager\CatalogContentElementParser', 'parseVisibilityPanels'];
$GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = ['CatalogManager\ChangeLanguageExtension', 'translateUrlParameters'];
$GLOBALS['TL_HOOKS']['getAttributesFromDca'][] = ['CatalogManager\CatalogWidgetAttributeParser', 'parseCatalogNavigationAreasWidget'];

$GLOBALS['TL_CATALOG_MANAGER']['CORE_TABLES'] = [];
$GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'] = [];
$GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] = [];
$GLOBALS['TL_CATALOG_MANAGER']['VISIBILITY_PANEL'] = FALSE;

$GLOBALS['TL_CATALOG_MANAGER']['tinyMCE'] = [ 'tinyMCE', 'tinyFlash' ];

$GLOBALS['TL_WRAPPERS']['stop'][] = 'fieldsetStop';
$GLOBALS['TL_WRAPPERS']['start'][] = 'fieldsetStart';
$GLOBALS['TL_WRAPPERS']['stop'][] = 'catalogVisibilityPanelStop';
$GLOBALS['TL_WRAPPERS']['start'][] = 'catalogVisibilityPanelStart';

$GLOBALS['TL_PERMISSIONS'][] = 'catalog';
$GLOBALS['TL_PERMISSIONS'][] = 'catalogp';
$GLOBALS['TL_PERMISSIONS'][] = 'filterform';
$GLOBALS['TL_PERMISSIONS'][] = 'filterformp';

$GLOBALS['BE_FFL']['catalogMessageWidget'] = 'CatalogManager\CatalogMessageWidget';
$GLOBALS['BE_FFL']['catalogTaxonomyWizard'] = 'CatalogManager\CatalogTaxonomyWizard';
$GLOBALS['BE_FFL']['catalogValueSetterWizard'] = 'CatalogManager\CatalogValueSetterWizard';
$GLOBALS['BE_FFL']['catalogDuplexSelectWizard'] = 'CatalogManager\CatalogDuplexSelectWizard';
$GLOBALS['BE_FFL']['catalogRelationRedirectWizard'] = 'CatalogManager\CatalogRelationRedirectWizard';
$GLOBALS['BE_FFL']['catalogFilterFieldSelectWizard'] = 'CatalogManager\CatalogFilterFieldSelectWizard';

$GLOBALS['TL_FFL']['catalogMessageForm'] = 'CatalogManager\CatalogMessageForm';
$GLOBALS['TL_FFL']['catalogFineUploader'] = 'CatalogManager\CatalogFineUploaderForm';

$GLOBALS['TL_CATALOG_MANAGER']['FIELD_TYPES'] = [
    'text' => [ 'dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField' ],
    'date' => [ 'dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField' ],
    'radio' => [ 'dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField' ],
    'hidden' => [ 'dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField' ],
    'number' => [ 'dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField' ],
    'select' => [ 'dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField' ],
    'message' => [ 'dcPicker' => 'message;', 'dcType' => 'dcPaletteField' ],
    'map' => [ 'dcPicker' => 'description;', 'dcType' => 'dcPaletteField' ],
    'upload' => [ 'dcPicker' => 'statement;', 'dcType' => 'dcPaletteField' ],
    'checkbox' => [ 'dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField' ],
    'textarea' => [ 'dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField' ],
    'dbColumn' => [ 'dcPicker' => 'useIndex;', 'dcType' => 'dcPaletteField' ],
    'fieldsetStart' => [  'dcPicker' => 'isHidden;', 'dcType' => 'dcPaletteLegend' ],
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
    'ctlg_entity_status_insert'   => [
        'recipients' => [ 'admin_email', 'raw_*', 'clean_*' ],
        'email_replyTo' => [ 'admin_email', 'raw_*', 'clean_*' ],
        'email_sender_name' => [ 'admin_email', 'raw_*', 'clean_*' ],
        'email_recipient_cc' => [ 'admin_email', 'raw_*', 'clean_*' ],
        'email_recipient_bcc' => [ 'admin_email', 'raw_*', 'clean_*' ],
        'email_sender_address' => [ 'admin_email', 'raw_*', 'clean_*' ],
        'email_subject' => [ 'admin_email', 'domain', 'raw_*', 'clean_*' ],
        'attachment_tokens' => [ 'raw_*', 'clean_*', 'field_*', 'table_*' ],
        'file_name' => [ 'admin_email', 'raw_*', 'clean_*', 'field_*', 'table_*' ],
        'file_content' => [ 'admin_email', 'raw_*', 'clean_*', 'field_*', 'table_*' ],
        'email_text' => [ 'admin_email', 'domain', 'raw_*', 'clean_*', 'field_*', 'table_*' ],
        'email_html' => [ 'admin_email', 'domain', 'raw_*', 'clean_*', 'field_*', 'table_*' ]
    ],
    'ctlg_entity_status_duplicate'   => [
        'recipients' => [ 'admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'email_replyTo' => [ 'admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'email_sender_name' => [ 'admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'email_recipient_cc' => [ 'admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'email_recipient_bcc' => [ 'admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'email_sender_address' => [ 'admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'email_subject' => [ 'admin_email', 'domain', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'attachment_tokens' => [ 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ],
        'file_name' => [ 'admin_email', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ],
        'file_content' => [ 'admin_email', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ],
        'email_text' => [ 'admin_email', 'domain', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ],
        'email_html' => [ 'admin_email', 'domain', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ]
    ],
    'ctlg_entity_status_update' => [
        'recipients' => [ 'admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'email_replyTo' => [ 'admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'email_sender_name' => [ 'admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'email_recipient_cc' => [ 'admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'email_recipient_bcc' => [ 'admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'email_sender_address' => [ 'admin_email', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'email_subject' => [ 'admin_email', 'domain', 'raw_*', 'clean_*', 'rawOld_*', 'cleanOld_*' ],
        'attachment_tokens' => [ 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ],
        'file_name' => [ 'admin_email', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ],
        'file_content' => [ 'admin_email', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ],
        'email_text' => [ 'admin_email', 'domain', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ],
        'email_html' => [ 'admin_email', 'domain', 'raw_*', 'clean_*', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ]
    ],
    'ctlg_entity_status_delete' => [
        'recipients' => [ 'admin_email', 'rawOld_*', 'cleanOld_*' ],
        'email_replyTo' => [ 'admin_email', 'rawOld_*', 'cleanOld_*' ],
        'email_sender_name' => [ 'admin_email', 'rawOld_*', 'cleanOld_*' ],
        'email_recipient_cc' => [ 'admin_email', 'rawOld_*', 'cleanOld_*' ],
        'email_recipient_bcc' => [ 'admin_email', 'rawOld_*', 'cleanOld_*' ],
        'email_sender_address' => [ 'admin_email', 'rawOld_*', 'cleanOld_*' ],
        'email_subject' => [ 'admin_email', 'domain', 'rawOld_*', 'cleanOld_*' ],
        'attachment_tokens' => [ 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ],
        'file_name' => [ 'admin_email', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ],
        'file_content' => [ 'admin_email', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ],
        'email_text' => [ 'admin_email', 'domain', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ],
        'email_html' => [ 'admin_email', 'domain', 'field_*', 'table_*', 'rawOld_*', 'cleanOld_*' ],
    ]
];

$GLOBALS['CM_ICON_SET'] = [
    'new' => 'system/modules/catalog-manager/assets/icons/new.svg',
    'pdf' => 'system/modules/catalog-manager/assets/icons/pdf.svg',
    'edit' => 'system/modules/catalog-manager/assets/icons/edit.svg',
    'copy' => 'system/modules/catalog-manager/assets/icons/copy.svg',
    'delete' => 'system/modules/catalog-manager/assets/icons/delete.svg'
];