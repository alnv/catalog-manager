<?php

if ( class_exists( 'NamespaceClassLoader' ) ) {

    NamespaceClassLoader::add( 'OceanCatalog', 'system/modules/oceanCatalog/library' );
}

if ( class_exists( 'NamespaceClassLoader' ) ) {

    NamespaceClassLoader::addClassMap([

        'OceanCatalog\tl_catalog' => 'system/modules/oceanCatalog/classes/tl_catalog.php',
        'OceanCatalog\tl_catalog_fields' => 'system/modules/oceanCatalog/classes/tl_catalog_fields.php',
    ]);
}