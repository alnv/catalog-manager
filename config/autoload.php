<?php

ClassLoader::addNamespace( 'CatalogManager' );

ClassLoader::addClasses([

    'CatalogManager\Toolkit' => 'system/modules/catalog-manager/library/alnv/Toolkit.php',
    'CatalogManager\tl_catalog' => 'system/modules/catalog-manager/classes/tl_catalog.php',
    'CatalogManager\Text' => 'system/modules/catalog-manager/library/alnv/fields/Text.php',
    'CatalogManager\Radio' => 'system/modules/catalog-manager/library/alnv/fields/Radio.php',
    'CatalogManager\DCAHelper' => 'system/modules/catalog-manager/library/alnv/DCAHelper.php',
    'CatalogManager\Select' => 'system/modules/catalog-manager/library/alnv/fields/Select.php',
    'CatalogManager\Hidden' => 'system/modules/catalog-manager/library/alnv/fields/Hidden.php',
    'CatalogManager\Upload' => 'system/modules/catalog-manager/library/alnv/fields/Upload.php',
    'CatalogManager\Number' => 'system/modules/catalog-manager/library/alnv/fields/Number.php',
    'CatalogManager\CSVBuilder' => 'system/modules/catalog-manager/library/alnv/CSVBuilder.php',
    'CatalogManager\SQLBuilder' => 'system/modules/catalog-manager/library/alnv/SQLBuilder.php',
    'CatalogManager\DCABuilder' => 'system/modules/catalog-manager/library/alnv/DCABuilder.php',
    'CatalogManager\YAMLParser' => 'system/modules/catalog-manager/library/alnv/YAMLParser.php',
    'CatalogManager\Checkbox' => 'system/modules/catalog-manager/library/alnv/fields/Checkbox.php',
    'CatalogManager\Textarea' => 'system/modules/catalog-manager/library/alnv/fields/Textarea.php',
    'CatalogManager\DCACallbacks' => 'system/modules/catalog-manager/library/alnv/DCACallbacks.php',
    'CatalogManager\DateInput' => 'system/modules/catalog-manager/library/alnv/fields/DateInput.php',
    'CatalogManager\DCAPermission' => 'system/modules/catalog-manager/library/alnv/DCAPermission.php',
    'CatalogManager\tl_catalog_fields' => 'system/modules/catalog-manager/classes/tl_catalog_fields.php',
    'CatalogManager\CatalogController' => 'system/modules/catalog-manager/library/alnv/CatalogController.php',
    'CatalogManager\ReviseRelatedTables' => 'system/modules/catalog-manager/library/alnv/ReviseRelatedTables.php',
    'CatalogManager\i18nCatalogTranslator' => 'system/modules/catalog-manager/library/alnv/i18nCatalogTranslator.php',
    'CatalogManager\UserPermissionExtension' => 'system/modules/catalog-manager/library/alnv/UserPermissionExtension.php',
    'CatalogManager\CatalogManagerInitializer' => 'system/modules/catalog-manager/library/alnv/CatalogManagerInitializer.php'
]);