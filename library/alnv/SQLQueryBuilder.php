<?php

namespace CatalogManager;

class SQLQueryBuilder extends CatalogController {


    private $strQuery = '';
    private $strTable = '';
    private $arrQuery = [];
    private $arrValues = [];
    private $arrMultipleValues = [];


    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
    }


    public function getQuery( $arrQuery ) {

        $this->arrValues = [];
        $this->arrQuery = $arrQuery;
        $this->strTable = $arrQuery['table'];

        $this->createSelectQuery();

        return $this->strQuery;
    }


    public function execute( $arrQuery ) {

        $this->getQuery( $arrQuery );

        return $this->Database->prepare( $this->strQuery )->execute( $this->arrValues );
    }


    public function tableExist( $strTable ) {

        if ( !$strTable || !$this->Database->tableExists( $strTable ) ) {

            return false;
        }

        return true;
    }


    public function getWhereQuery( $arrQuery ) {

        $this->arrValues = [];
        $this->arrQuery = $arrQuery;
        $this->strTable = $arrQuery['table'];

        return $this->createWhereStatement();
    }


    public function getValues() {

        return $this->arrValues;
    }

    
    protected function createSelectQuery() {

        $this->strQuery = sprintf( 'SELECT %s FROM %s%s%s%s%s%s',

            $this->createSelectionStatement(),
            $this->strTable,
            $this->createJoinStatement(),
            $this->createWhereStatement(),
            $this->createHavingDistanceStatement(),
            $this->createOrderByStatement(),
            $this->createPaginationStatement()
        );
    }


    protected function equal( $strField ) {

        return sprintf( '%s.`%s` = ?', $this->strTable, $strField );
    }


    protected function not( $strField ) {

        return sprintf( '%s.`%s` != ?', $this->strTable, $strField );
    }


    protected function regexp( $strField ) {

        return sprintf( 'LOWER(CAST(%s.`%s` AS CHAR)) REGEXP LOWER(?)', $this->strTable, $strField );
    }


    protected function findInSet( $strField ) {

        return sprintf( 'FIND_IN_SET(?,LOWER(CAST(%s.`%s` AS CHAR)))', $this->strTable, $strField );
    }


    protected function gt( $strField ) {

        return sprintf( 'LOWER(CAST(%s.`%s` AS SIGNED)) > ?', $this->strTable, $strField );
    }


    protected function gte( $strField ) {

        return sprintf( 'LOWER(CAST(%s.`%s` AS SIGNED)) >= ?', $this->strTable, $strField );
    }


    protected function lt( $strField ) {

        return sprintf( 'LOWER(CAST(%s.`%s` AS SIGNED)) < ?', $this->strTable, $strField );
    }


    protected function lte( $strField ) {

        return sprintf( 'LOWER(CAST(%s.`%s` AS SIGNED)) <= ?', $this->strTable, $strField );
    }


    protected function contain( $strField ) {

        $strPlaceholder = $this->arrMultipleValues[ $strField ] ? implode ( ',', array_fill( 0, $this->arrMultipleValues[ $strField ], '?' ) ) : '?';

        return sprintf( 'LOWER(%s.`%s`) IN ('. $strPlaceholder .')', $this->strTable, $strField );
    }


    protected function between( $strField ) {

        return sprintf( 'LOWER(%s.`%s`) BETWEEN ? AND ?', $this->strTable, $strField );
    }


    protected function isEmpty( $strField ) {

        return sprintf( "%s.%s IS NULL OR %s.%s = ?", $this->strTable, $strField, $this->strTable, $strField );
    }


    protected function isNotEmpty( $strField ) {

        return sprintf( "%s.%s != ?", $this->strTable, $strField );
    }


    protected function createSelectionStatement() {

        $strSelectionStatement = '*';

        if ( !$this->arrQuery['joins'] || empty( $this->arrQuery['joins'] ) || !is_array( $this->arrQuery['joins'] ) ) {

            return $strSelectionStatement . $this->getDistanceField();
        }

        $strSelectionStatement = sprintf( '%s.*', $this->strTable );

        foreach ( $this->arrQuery['joins'] as $intIndex => $arrJoin ) {

            if ( empty( $arrJoin ) ) continue;

            if ( !$intIndex ) $strSelectionStatement .= ',';

            $arrColumnAliases = [];
            $arrForeignColumns = $this->getForeignColumnsByTablename( $arrJoin['onTable'] );

            foreach ( $arrForeignColumns as $strForeignColumn ) {

                $arrColumnAliases[] = sprintf( '%s.`%s` AS %s', $arrJoin['onTable'], $strForeignColumn, $arrJoin['onTable']. ( ucfirst( $strForeignColumn ) ) );
            }

            $strSelectionStatement .= ( $intIndex ? ',' : '' ) . implode( ',' , $arrColumnAliases );
        }

        return $strSelectionStatement . $this->getDistanceField();
    }


    protected function createHavingDistanceStatement() {

        if ( !$this->arrQuery['distance'] || empty( $this->arrQuery['distance'] ) || !is_array( $this->arrQuery['distance'] ) ) {

            return '';
        }

        return sprintf( ' HAVING _distance < %s', $this->arrQuery['distance']['value'] );
    }


    protected function createJoinStatement() {

        $strJoinStatement = '';

        if ( !$this->arrQuery['joins'] || empty( $this->arrQuery['joins'] ) || !is_array( $this->arrQuery['joins'] ) ) {

            return $strJoinStatement;
        }

        foreach ( $this->arrQuery['joins'] as $intIndex => $arrJoin ) {

            if ( !$arrJoin['table'] || !$arrJoin['field'] || !$arrJoin['onTable'] || !$arrJoin['onField'] ) {

                continue;
            }

            if ( $arrJoin['multiple'] ) {

                $strJoinStatement .= sprintf( ( $intIndex ? ' ' : '' ) . ' JOIN %s ON FIND_IN_SET(%s.`%s`,%s.`%s`)', $arrJoin['onTable'], $arrJoin['onTable'], $arrJoin['onField'], $arrJoin['table'], $arrJoin['field'] );
            }

            else {

                $strJoinStatement .= sprintf( ( $intIndex ? ' ' : '' ) . ' JOIN %s ON %s.`%s` = %s.`%s`', $arrJoin['onTable'], $arrJoin['table'], $arrJoin['field'], $arrJoin['onTable'], $arrJoin['onField'] );
            }
        }

        return $strJoinStatement;
    }


    // @todo improve
    protected function createWhereStatement() {

        $strWhereStatement = '';

        if ( !$this->arrQuery['where'] || empty( $this->arrQuery['where'] ) || !is_array( $this->arrQuery['where'] ) ) {

            return $strWhereStatement;
        }

        $strWhereStatement .= ' WHERE ';

        foreach ( $this->arrQuery['where'] as $intIndex => $arrQueries ) {

            if ( $intIndex ) $strWhereStatement .= ' AND ';

            if ( !empty( $arrQueries[0] ) && is_array( $arrQueries[0] ) ) {

                $intOrIndex = 0;

                $strWhereStatement .= '(';

                foreach ( $arrQueries as $strKey => $varValue ) {

                    if ( $intOrIndex ) $strWhereStatement .= ' OR ';

                    if ( !$varValue['operator'] ) continue;

                    if ( is_bool( $varValue['multiple'] ) &&  $varValue['multiple'] == true ) {

                        if ( !empty( $varValue ) && is_array( $varValue['value'] ) ) {

                            $intValues = count( $varValue['value'] ) -1;

                            foreach ( $varValue['value'] as $intValueIndex => $varQueryValue ) {

                                $this->setValue( $varQueryValue, $varValue['field'] );
                                $strWhereStatement .= call_user_func_array( [ 'SQLQueryBuilder', $varValue['operator'] ], [ $varValue['field'] ] ) . ( $intValues !==  $intValueIndex ? ' OR ' : '' );
                            }
                        }
                    }

                    else {

                        $this->setValue( $varValue['value'], $varValue['field'] );
                        $strWhereStatement .= call_user_func_array( [ 'SQLQueryBuilder', $varValue['operator'] ], [ $varValue['field'] ] );
                    }

                    $intOrIndex++;
                }

                $strWhereStatement .= ')';
            }

            else {

                if ( $arrQueries['operator'] ) {

                    if ( is_bool( $arrQueries['multiple'] ) &&  $arrQueries['multiple'] == true ) {

                        if ( !empty( $arrQueries ) && is_array( $arrQueries['value'] ) ) {

                            $intValues = count( $arrQueries['value'] ) -1;
                            
                            foreach ( $arrQueries['value'] as $intValueIndex => $varQueryValue ) {

                                $this->setValue( $varQueryValue, $arrQueries['field'] );
                                $strWhereStatement .= call_user_func_array( [ 'SQLQueryBuilder', $arrQueries['operator'] ], [ $arrQueries['field'] ] ) . ( $intValues !==  $intValueIndex ? ' OR ' : '' );
                            }
                        }
                    }

                    else {

                        $this->setValue( $arrQueries['value'], $arrQueries['field'] );
                        $strWhereStatement .= call_user_func_array( [ 'SQLQueryBuilder', $arrQueries['operator'] ], [ $arrQueries['field'] ] );
                    }
                }
            }
        }

        return $strWhereStatement;
    }


    protected function createPaginationStatement() {

        if ( !$this->arrQuery['pagination'] || empty( $this->arrQuery['pagination'] ) || !is_array( $this->arrQuery['pagination'] ) ) {

            return '';
        }

        $strOffset = $this->arrQuery['pagination']['offset'] ? intval( $this->arrQuery['pagination']['offset'] ) : 0;
        $strLimit = $this->arrQuery['pagination']['limit'] ? intval( $this->arrQuery['pagination']['limit'] ) : 1000;

        return sprintf( ' LIMIT %s, %s', $strOffset, $strLimit );
    }


    protected function createOrderByStatement() {

        $arrOrderByStatements = [];
        $arrAllowedModes = [ 'DESC', 'ASC' ];

        if ( !$this->arrQuery['orderBy'] || empty( $this->arrQuery['orderBy'] ) || !is_array( $this->arrQuery['orderBy'] ) ) {

            return '';
        }

        foreach ( $this->arrQuery['orderBy'] as $intIndex => $arrOrderBy ) {

            if ( !$arrOrderBy['order'] ) $arrOrderBy['order'] = 'DESC';

            if ( !$arrOrderBy['field'] || !in_array( $arrOrderBy['order'] , $arrAllowedModes )) continue;

            $arrOrderByStatements[] = sprintf( '%s.`%s` %s', $this->strTable, $arrOrderBy['field'], $arrOrderBy['order'] );
        }

        if ( empty( $arrOrderByStatements ) ) {

            return '';
        }

        return ' ORDER BY ' . implode( ',', $arrOrderByStatements );
    }


    private function setValue( $varValue, $strFieldname = '' ) {

        if ( is_array( $varValue ) ) {

            foreach ( $varValue as $strValue ) {

                $this->arrValues[] = $strValue;
            }

            $this->arrMultipleValues[ $strFieldname ] = count( $varValue );
        }

        else {

            $this->arrValues[] = $varValue;
        }
    }


    private function getForeignColumnsByTablename( $strTable ) {

        if ( !$strTable || !$this->Database->tableExists( $strTable ) ) {

            return [];
        }

        return Toolkit::parseColumns( $this->Database->listFields( $strTable ) );
    }


    private function getDistanceField() {

        if ( !$this->arrQuery['distance'] || empty( $this->arrQuery['distance'] ) || !is_array( $this->arrQuery['distance'] ) ) {

            return '';
        }

        return sprintf(

            ",3956 * 1.6 * 2 * ASIN(SQRT(POWER(SIN((%s-abs(%s)) * pi()/180 / 2),2) + COS(%s * pi()/180) * COS(abs(%s) *  pi()/180) * POWER( SIN( (%s-%s) *  pi()/180 / 2 ), 2 ))) AS _distance",
            $this->arrQuery['distance']['latCord'],
            $this->arrQuery['distance']['latField'],
            $this->arrQuery['distance']['latCord'],
            $this->arrQuery['distance']['latField'],
            $this->arrQuery['distance']['lngCord'],
            $this->arrQuery['distance']['lngField']
        );
    }
}