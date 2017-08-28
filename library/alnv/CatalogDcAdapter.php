<?php

namespace CatalogManager;

class CatalogDcAdapter extends CatalogController {


    public function __construct() {

        parent::__construct();
    }


    public function initialize( $strTablename ) {

        if ( $this->loadDc( $strTablename ) ) {

            $objDcExtractor = new CatalogDcExtractor();
            $objDcExtractor->initialize( $strTablename );
            $GLOBALS['TL_DCA'][ $strTablename ] = $objDcExtractor->convertCatalogToDataContainer();
        }
    }


    protected function loadDc( $strTablename ) {

        return in_array( $strTablename, $GLOBALS['TL_CATALOG_MANAGER']['CORE_TABLES'] ) && ( \Input::get('do') != 'catalog-manager' || \Input::get('table') == 'tl_catalog_fields' );
    }
}