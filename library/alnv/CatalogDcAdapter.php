<?php

namespace CatalogManager;

class CatalogDcAdapter extends CatalogController {


    public function __construct() {

        parent::__construct();
    }


    public function initialize( $strTablename ) {

        if ( $this->shouldLoadDc( $strTablename ) ) {

            $objDcExtractor = new CatalogDcExtractor();
            $objDcExtractor->initialize( $strTablename );
            $GLOBALS['TL_DCA'][ $strTablename ] = $objDcExtractor->convertCatalogToDataContainer();
        }
    }


    protected function shouldLoadDc( $strTablename ) {

        $blnIsCoreTable = in_array( $strTablename, $GLOBALS['TL_CATALOG_MANAGER']['CORE_TABLES'] );

        if ( TL_MODE == 'FE' && !$blnIsCoreTable ) {

            $objDatabase = \Database::getInstance();
            $objCatalog = $objDatabase->prepare( 'SELECT id FROM tl_catalog WHERE tablename = ?' )->limit(1)->execute( $strTablename );

            return $objCatalog->numRows ? true : false;
        }

        return $blnIsCoreTable && ( \Input::get('do') != 'catalog-manager' || \Input::get('table') == 'tl_catalog_fields' );
    }
}