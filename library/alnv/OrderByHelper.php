<?php

namespace CatalogManager;

class OrderByHelper extends CatalogController {


    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
    }


    protected function getCatalogTablename( $objWidget ) {
        
        $objModule = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE id = ?', $objWidget->strTable ) )->limit(1)->execute( $objWidget->currentRecord );

        if ( $objModule->numRows ) {

            if ( $objModule->catalogTablename ) return $objModule->catalogTablename;

            if ( $objModule->dbTable ) return $objModule->dbTable;
        }

        return '';
    }


    public function getSortableFields( $objWidget ) {

        $arrReturn = [];
        $strTablename = $this->getCatalogTablename( $objWidget );

        if ( !$strTablename ) return $arrReturn;

        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize( $strTablename );
        $arrFields = $objCatalogFieldBuilder->getCatalogFields( true, null );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( in_array( $arrField['type'], [ 'fieldsetStart', 'fieldsetStop', 'map', 'upload', 'textarea' ] ) ) {

                continue;
            }

            $arrReturn[ $strFieldname ] = Toolkit::getLabelValue( $arrField['_dcFormat']['label'], $strFieldname );
        }

        return $arrReturn;
    }


    public function getOrderByItems() {

        return [ 'ASC' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['asc'], 'DESC' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['desc'] ];
    }


    public function getOrderByQuery( $arrOrderBy, $strTable ) {

        $arrAllowedModes = [ 'DESC', 'ASC' ];

        foreach ( $arrOrderBy as $arrOrder ) {

            if ( !$arrOrder['value'] ) $arrOrder['value'] = 'DESC';

            if ( !$arrOrder['value'] || !in_array( $arrOrder['value'] , $arrAllowedModes )) continue;

            $arrOrderByStatements[] = sprintf( '%s.`%s` %s', $strTable, $arrOrder['key'], $arrOrder['value'] );
        }

        if ( empty( $arrOrderByStatements ) ) return '';

        return 'ORDER BY ' . implode( ',', $arrOrderByStatements );
    }
}