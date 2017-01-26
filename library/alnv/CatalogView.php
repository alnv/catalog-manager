<?php

namespace CatalogManager;

class CatalogView extends CatalogController {

    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
    }

    public function getCatalogByTablename( $strTablename ) {

        if ( !$strTablename ) {

            return [];
        }

        return $this->Database->prepare( 'SELECT * FROM tl_catalog WHERE tablename = ?' )->limit(1)->execute( $strTablename )->row();
    }

    public function getCatalogFieldsByCatalogID( $strID ) {

        $arrFields = [];

        if ( !$strID ) {

            return $arrFields;
        }

        $objFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ? ORDER BY sorting' )->execute( $strID );

        while ( $objFields->next() ) {

            $arrFields[ $objFields->fieldname ] = $objFields->row();
        }

        return $arrFields;
    }

    public function getCatalogDataByTable( $strTable, $arrOptions = [] ) {

        if ( !$strTable || !$this->Database->tableExists( $strTable ) ) {

            return [];
        }

        if ( !$arrOptions['table'] ) {

            $arrOptions['table'] = $strTable;
        }

        $arrCatalogData = [];
        $objSQLQueryBuilder = new SQLQueryBuilder();
        $objQueryBuilderResults = $objSQLQueryBuilder->execute( $arrOptions );

        while ( $objQueryBuilderResults->next() ) {
            
            $arrCatalogData[ $objQueryBuilderResults->id ] = $objQueryBuilderResults->row();
        }
        
        return $arrCatalogData;
    }
}