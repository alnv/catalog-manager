<?php

namespace CatalogManager;

class CatalogDCAExtractor extends CatalogController {


    protected $strTable = '';
    protected $strOrderBy = '';


    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
    }


    public function extract( $strTable ) {

        $this->strTable = $strTable;
        $objModule = $this->Database->prepare( 'SELECT * FROM tl_catalog WHERE tablename = ? LIMIT 1' )->execute( $this->strTable );

        if ( $objModule->numRows ) {

            $arrSorting = [

                'mode' => $objModule->mode,
                'flag' => $objModule->flag,
                'fields' => Toolkit::deserialize( $objModule->sortingFields )
            ];

            $this->extractDCASorting( $arrSorting );

            return null;
        }

        if ( !isset( $GLOBALS['loadDataContainer'][ $this->strTable ] ) ) {

            $this->loadDataContainer( $this->strTable );
        }

        if ( $GLOBALS['TL_DCA'][ $this->strTable ]['config']['dataContainer'] == 'File' ) {

            return null;
        }

        if ( !empty( $GLOBALS['TL_DCA'][ $this->strTable ]['list'] ) && is_array( $GLOBALS['TL_DCA'][ $this->strTable ]['list'] ) ) {

            if ( !empty( $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting'] ) && is_array( $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting'] ) ) {

                $this->extractDCASorting( $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting'] );
            }
        }
    }


    public function getOrderByStatement() {

        return $this->strOrderBy;
    }


    public function hasOrderByStatement() {

        return !Toolkit::isEmpty( $this->strOrderBy );
    }


    protected function extractDCASorting( $arrSorting ) {

        $arrTemps = [];
        $arrOrderBy = [];
        $intFlag = Toolkit::isEmpty( $arrSorting['flag'] ) ? 1 : (int) $arrSorting['flag'];
        $arrFields = !empty( $arrSorting['fields'] ) && is_array( $arrSorting['fields'] ) ? $arrSorting['fields'] : [];
        $strOrder = $intFlag % 2 ? 'ASC' : 'DESC';

        foreach ( $arrFields as $strField ) {

            if ( in_array( $strField, $arrTemps ) ) {

                continue;
            }

            else {

                $arrTemps[] = $strField;
            }

            if ( stripos( $strField, 'asc' ) || stripos( $strField, 'desc' ) ) {

                $arrOrderBy[] = $strField;

                continue;
            }

            if ( $this->Database->fieldExists( $strField, $this->strTable ) ) {

                $arrOrderBy[] = $strField . ' ' . $strOrder;
            }
        }

        $this->strOrderBy = implode( ',' , $arrOrderBy );
    }
}