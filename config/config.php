<?php

$GLOBALS['BE_MOD']['system']['catalog-manager'] = [

    'name' => 'catalog-manager',

    'tables' => [
        
        'tl_catalog',
        'tl_catalog_fields'
    ]
];

array_insert( $GLOBALS['FE_MOD'], 3, [

    'catalog-manager' => [

        'catalogUniversalView' => 'ModuleUniversalView',
        'catalogFilter' => 'ModuleCatalogFilter'
    ]
]);

if ( TL_MODE == 'BE' ) {

    $GLOBALS['TL_JAVASCRIPT']['catalogManagerBackendExtension'] = $GLOBALS['TL_CONFIG']['debugMode']
        ? 'system/modules/catalog-manager/assets/BackendExtension.js'
        : 'system/modules/catalog-manager/assets/BackendExtension.js';

    $GLOBALS['TL_CSS']['catalogManagerBackendExtension'] = $GLOBALS['TL_CONFIG']['debugMode']
        ? 'system/modules/catalog-manager/assets/Widgets.css'
        : 'system/modules/catalog-manager/assets/Widgets.css';
}

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = [ 'CatalogManager\UserPermissionExtension', 'initialize' ];
$GLOBALS['TL_HOOKS']['initializeSystem'][] =[ 'CatalogManager\CatalogManagerInitializer', 'initialize' ];
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = [ 'CatalogManager\MemberPermissionExtension', 'initialize' ];

$GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'] = [];
$GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] = [];

$GLOBALS['TL_WRAPPERS']['stop'][] = 'fieldsetStop';
$GLOBALS['TL_WRAPPERS']['start'][] = 'fieldsetStart';

$GLOBALS['TL_PERMISSIONS'][] = 'catalog';
$GLOBALS['TL_PERMISSIONS'][] = 'catalogp';

$GLOBALS['BE_FFL']['catalogOrderByWizard'] = 'CatalogManager\CatalogOrderByWizard';
$GLOBALS['BE_FFL']['catalogMessageWidget'] = 'CatalogManager\CatalogMessageWidget';
$GLOBALS['BE_FFL']['catalogTaxonomyWizard'] = 'CatalogManager\CatalogTaxonomyWizard';
$GLOBALS['BE_FFL']['catalogRelationWizard'] = 'CatalogManager\CatalogRelationWizard';

$GLOBALS['TL_FFL']['catalogMessageForm'] = 'CatalogManager\CatalogMessageForm';