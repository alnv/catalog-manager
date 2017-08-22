<?php

namespace CatalogManager;

class CatalogDCAExtractor extends CatalogController {


    protected $strTable = '';
    protected $strOrderBy = '';


    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
    }


    public function initialize( $strTablename ) {

        $this->strTable = $strTablename;
    }


    public function getCatalogValuesFromDataContainerArray() {

        $arrReturn = [];

        \Controller::loadLanguageFile( $this->strTable );
        \Controller::loadDataContainer( $this->strTable );

        $arrDataContainer = $GLOBALS['TL_DCA'][ $this->strTable ];

        if ( $arrDataContainer['config']['pTable'] ) {

            $arrReturn['pTable'] = $arrDataContainer['config']['pTable'];
        }

        if ( is_array( $arrDataContainer['config']['ctable'] ) && !empty( $arrDataContainer['config']['ctable'] ) ) {

            if ( in_array( 'tl_content', $arrDataContainer['config']['ctable'] ) ) {

                $arrReturn['addContentElements'] = '1';
            }

            $arrReturn['cTables'] = serialize( $arrDataContainer['config']['ctable'] );
        }

        if ( is_array( $arrDataContainer['list']['sorting'] ) ) {

            if ( isset( $arrDataContainer['list']['sorting']['mode'] ) && in_array( $arrDataContainer['list']['sorting']['mode'], Toolkit::$arrModeTypes ) ) {

                $arrReturn['mode'] = $arrDataContainer['list']['sorting']['mode'];
            }

            if ( isset( $arrDataContainer['list']['sorting']['flag'] ) ) {

                $arrReturn['flag'] = $arrDataContainer['list']['sorting']['flag'];
            }

            if ( isset( $arrDataContainer['list']['sorting']['panelLayout'] ) && is_string( $arrDataContainer['list']['sorting']['panelLayout'] ) ) {

                $arrPanelLayout = preg_split( '/(,|;)/', $arrDataContainer['list']['sorting']['panelLayout'] );
                $arrReturn['panelLayout'] = serialize( $arrPanelLayout );
            }

            if ( is_array( $arrDataContainer['list']['sorting']['fields'] ) && !empty( $arrDataContainer['list']['sorting']['fields'] ) ) {

                $arrFields = [];
                $arrSortingFields = $arrDataContainer['list']['sorting']['fields'];

                foreach ( $arrSortingFields as $strField ) {

                    $strUpperCaseField = strtoupper( $strField );

                    if ( stripos( $strUpperCaseField, 'ASC' ) || stripos( $strUpperCaseField, 'DESC' ) ) {

                        $arrFieldParameter = explode( ' ' , $strField );

                        if ( !Toolkit::isEmpty( $arrFieldParameter[0] ) ) {

                            $arrFields[] = $arrFieldParameter[0];
                        }

                        continue;
                    }

                    $arrFields[] = $strField;
                }

                $arrReturn['sortingFields'] = serialize( $arrFields );
            }
        }

        if ( is_array( $arrDataContainer['list']['label'] ) ) {

            if ( isset( $arrDataContainer['list']['label']['format'] ) ) {

                $arrReturn['format'] = $arrDataContainer['list']['label']['format'];
            }

            if ( $arrDataContainer['list']['label']['showColumns'] ) {

                $arrReturn['showColumns'] = '1';
            }

            if ( is_array( $arrDataContainer['list']['label']['fields'] ) && !empty( $arrDataContainer['list']['label']['fields'] ) ) {

                $arrReturn['labelFields'] = serialize( $arrDataContainer['list']['label']['fields'] );
            }
        }

        if ( is_array( $arrDataContainer['list']['operations'] ) ) {

            $arrOperators = [];
            $arrOperatorParameter = array_keys( $arrDataContainer['list']['operations'] );

            if ( is_array( $arrOperatorParameter ) && !empty( $arrOperatorParameter ) ) {

                foreach ( $arrOperatorParameter as $strOperator ) {

                    if ( in_array( $strOperator, Toolkit::$arrOperators ) ) {

                        $arrOperators[] = $strOperator;
                    }
                }

                $arrReturn['operations'] = serialize( $arrOperators );
            }
        }

        return $arrReturn;
    }


    public function extract() {

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

        $this->loadDataContainer( $this->strTable );

        if ( $GLOBALS['TL_DCA'][ $this->strTable ]['config']['dataContainer'] == 'File' ) {

            return null;
        }

        if ( !empty( $GLOBALS['TL_DCA'][ $this->strTable ]['list'] ) && is_array( $GLOBALS['TL_DCA'][ $this->strTable ]['list'] ) ) {

            if ( !empty( $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting'] ) && is_array( $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting'] ) ) {

                $arrSorting = $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting'];

                if ( !Toolkit::isEmpty( $arrSorting['mode'] ) && in_array( $arrSorting['mode'], [ 5, 6 ] ) && empty( $arrSorting['fields'] ) ) {

                    $arrSorting['fields'] = ['sorting'];
                }

                $this->extractDCASorting( $arrSorting );
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

            $strUpperCaseField = strtoupper( $strField );

            if ( stripos( $strUpperCaseField, 'ASC' ) || stripos( $strUpperCaseField, 'DESC' ) ) {

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