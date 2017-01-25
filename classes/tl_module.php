<?php

namespace CatalogManager;

class tl_module extends \Backend {

    public function getCatalogs() {

        $arrReturn = [];

        if ( !empty( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] ) && is_array( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] ) ) {

            foreach ( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] as $arrCatalog ) {

                $arrReturn[ $arrCatalog['tablename'] ] = $arrCatalog['name'];
            }
        }

        return $arrReturn;
    }
}
