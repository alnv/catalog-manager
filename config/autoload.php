<?php

if ( class_exists( 'ClassLoader' ) ) {

    ClassLoader::addNamespace( 'CatalogManager' );
}

if ( class_exists( 'NamespaceClassLoader' ) ) {

    NamespaceClassLoader::addClassMap([

        'CatalogManager\Toolkit' => 'system/modules/catalog-manager/library/alnv/Toolkit.php',
        'CatalogManager\tl_catalog' => 'system/modules/catalog-manager/classes/tl_catalog.php',
        'CatalogManager\Text' => 'system/modules/catalog-manager/library/alnv/fields/Text.php',
        'CatalogManager\Date' => 'system/modules/catalog-manager/library/alnv/fields/Date.php',
        'CatalogManager\Radio' => 'system/modules/catalog-manager/library/alnv/fields/Radio.php',
        'CatalogManager\Select' => 'system/modules/catalog-manager/library/alnv/fields/Select.php',
        'CatalogManager\Upload' => 'system/modules/catalog-manager/library/alnv/fields/Upload.php',
        'CatalogManager\Number' => 'system/modules/catalog-manager/library/alnv/fields/Number.php',
        'CatalogManager\SQLBuilder' => 'system/modules/catalog-manager/library/alnv/SQLBuilder.php',
        'CatalogManager\DCABuilder' => 'system/modules/catalog-manager/library/alnv/DCABuilder.php',
        'CatalogManager\Checkbox' => 'system/modules/catalog-manager/library/alnv/fields/Checkbox.php',
        'CatalogManager\Textarea' => 'system/modules/catalog-manager/library/alnv/fields/Textarea.php',
        'CatalogManager\CatalogManager' => 'system/modules/catalog-manager/library/alnv/CatalogManager.php',
        'CatalogManager\tl_catalog_fields' => 'system/modules/catalog-manager/classes/tl_catalog_fields.php',
        'CatalogManager\InitializeSystem' => 'system/modules/catalog-manager/library/alnv/InitializeSystem.php',
    ]);
}