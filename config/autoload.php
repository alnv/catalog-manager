<?php

if ( class_exists( 'NamespaceClassLoader' ) ) {

    NamespaceClassLoader::add( 'oceanCatalog', 'system/modules/oceanCatalog/library' );
}

if ( class_exists( 'NamespaceClassLoader' ) ) {

    NamespaceClassLoader::addClassMap([

        'oceanCatalog\tl_catalog' => 'system/modules/oceanCatalog/classes/tl_catalog.php',
        'oceanCatalog\tl_catalog_fields' => 'system/modules/oceanCatalog/classes/tl_catalog_fields.php',
    ]);
}