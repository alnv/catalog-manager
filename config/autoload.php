<?php

if ( class_exists( 'NamespaceClassLoader' ) ) {

    NamespaceClassLoader::add( 'CatalogManager', 'system/modules/catalog-manager/library' );
}

if ( class_exists( 'NamespaceClassLoader' ) ) {

    NamespaceClassLoader::addClassMap([

        'CatalogManager\tl_catalog' => 'system/modules/catalog-manager/classes/tl_catalog.php',
        'CatalogManager\tl_catalog_fields' => 'system/modules/catalog-manager/classes/tl_catalog_fields.php',
    ]);
}