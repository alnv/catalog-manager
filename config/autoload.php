<?php

if ( class_exists( 'ClassLoader' ) ) {

    ClassLoader::addNamespace( 'CatalogManager' );
}

if ( class_exists( 'NamespaceClassLoader' ) ) {

    NamespaceClassLoader::addClassMap([

        'CatalogManager\Toolkit' => 'system/modules/catalog-manager/library/alnv/Toolkit.php',
        'CatalogManager\tl_catalog' => 'system/modules/catalog-manager/classes/tl_catalog.php',
        'CatalogManager\SQLBuilder' => 'system/modules/catalog-manager/library/alnv/SQLBuilder.php',
        'CatalogManager\CatalogManager' => 'system/modules/catalog-manager/library/alnv/CatalogManager.php',
        'CatalogManager\tl_catalog_fields' => 'system/modules/catalog-manager/classes/tl_catalog_fields.php',
        'CatalogManager\InitializeSystem' => 'system/modules/catalog-manager/library/alnv/InitializeSystem.php',
    ]);
}