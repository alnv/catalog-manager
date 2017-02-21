<?php

namespace CatalogManager;

class tl_page extends \Backend {


    public function getCatalogTables( \DataContainer $dc ) {

        $arrReturn = [];
        $objCatalogs = $this->Database->prepare( 'SELECT * FROM tl_catalog' )->execute();

        while ( $objCatalogs->next() ) {

            $arrReturn[ $objCatalogs->tablename ] = $objCatalogs->name ? $objCatalogs->name : $objCatalogs->tablename;
        }

        return $arrReturn;
    }


    public function getCatalogColumn( \DataContainer $dc ) {

        $arrReturn = [];
        $strTable = $dc->activeRecord->catalogCatalogTable;

        if ( $strTable && $this->Database->tableExists( $strTable ) ) {

            $arrColumns = $this->Database->listFields( $strTable );
            $arrReturn = Toolkit::parseColumns( $arrColumns );
        }

        return $arrReturn;
    }
}