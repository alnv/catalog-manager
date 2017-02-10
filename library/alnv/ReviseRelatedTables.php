<?php

namespace CatalogManager;

class ReviseRelatedTables extends \Controller {


    private $arrErrorTables = [];


    public function __construct() {

        $this->import('Database');
    }


    public function reviseCatalogTables( $strTable, $strPTable, $arrCTables ) {

        $objCatalogDb = $this->Database->prepare( 'SELECT id FROM tl_catalog WHERE tablename = ?' )->execute( $strTable );

        if ( !$objCatalogDb->count() ) {

            return false;
        }

        if ( $strPTable ) {

            if ( $GLOBALS['TL_DCA'][ $strTable ]['config']['dynamicPtable'] ) {

                $objStmt = $this->Database->prepare( sprintf( ' SELECT * FROM %s  WHERE ptable = ? AND NOT EXISTS( SELECT * FROM %s WHERE %s.pid = %s.id )', $strTable, $strPTable, $strTable, $strPTable ) )->execute( $strPTable );
            }

            else {

                $objStmt = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE NOT EXISTS( SELECT * FROM %s WHERE %s.pid = %s.id )', $strTable, $strPTable, $strTable, $strPTable ) )->execute();
            }

            if ( $objStmt->count() > 0 ) {

                $this->arrErrorTables[] = $strPTable;

                return true;
            }
        }

        if ( !empty( $arrCTables ) && is_array( $arrCTables ) ) {

            foreach ( $arrCTables as $v ) {

                if ( $v ) {

                    if ( !isset( $GLOBALS['loadDataContainer'][$v] ) ) {

                        $this->loadDataContainer($v);
                    }

                    if ( $GLOBALS['TL_DCA'][$v]['config']['dynamicPtable'] ) {

                        $objStmt = $this->Database->prepare( sprintf( ' SELECT * FROM %s  WHERE ptable = ? AND NOT EXISTS( SELECT * FROM %s WHERE %s.pid = %s.id )', $v, $strTable, $v, $strTable ) )->execute( $v );
                    }

                    else {

                        $objStmt = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE NOT EXISTS( SELECT * FROM %s WHERE %s.pid = %s.id )', $v, $strTable, $v, $strTable ) )->execute();
                    }

                    if ( $objStmt->count() > 0 ) {

                        $this->arrErrorTables[] = $v;

                        return true;
                    }
                }
            }
        }

        return false;
    }

    
    public function getErrorTables() {

        return $this->arrErrorTables;
    }
}