<?php

namespace CatalogManager;

class tl_page extends \Backend {


    private $arrCatalogCache = [];


    public function getCatalogTables( \DataContainer $dc ) {

        if ( !empty( $this->arrCatalogCache ) && is_array( $this->arrCatalogCache ) ) {
            
            return $this->arrCatalogCache;
        }

        $objCatalogs = $this->Database->prepare( 'SELECT * FROM tl_catalog' )->execute();

        while ( $objCatalogs->next() ) {

            $this->arrCatalogCache[ $objCatalogs->tablename ] = $objCatalogs->name ? $objCatalogs->name . ' [' . $objCatalogs->tablename . ']' : $objCatalogs->tablename;
        }

        return $this->arrCatalogCache;
    }
}