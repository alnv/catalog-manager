<?php

if ( class_exists( 'NamespaceClassLoader' ) ) {

    NamespaceClassLoader::add( 'CatalogMaker', 'system/modules/catalog-maker/library' );
}

if ( class_exists( 'NamespaceClassLoader' ) ) {

    NamespaceClassLoader::addClassMap([

        'CatalogMaker\tl_catalog' => 'system/modules/catalog-maker/classes/tl_catalog.php',
        'CatalogMaker\tl_catalog_fields' => 'system/modules/catalog-maker/classes/tl_catalog_fields.php',
    ]);
}