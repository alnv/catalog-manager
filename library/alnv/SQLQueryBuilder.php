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

            $strType = $arrJoin['type'] ? $arrJoin['type'] : 'JOIN';

            if ( !$arrJoin['table'] || !$arrJoin['field'] || !$arrJoin['onTable'] || !$arrJoin['onField'] ) {

                continue;
            }

            if ( $arrJoin['multiple'] ) {

                $strJoinStatement .= sprintf( ( $intIndex ? ' ' : '' ) . ' %s %s ON FIND_IN_SET(%s.`%s`,%s.`%s`)', $strType, $arrJoin['onTable'], $arrJoin['onTable'], $arrJoin['onField'], $arrJoin['table'], $arrJoin['field'] );
            }

            else {

                $strJoinStatement .= sprintf( ( $intIndex ? ' ' : '' ) . ' %s %s ON %s.`%s` = %s.`%s`', $strType, $arrJoin['onTable'], $arrJoin['table'], $arrJoin['field'], $arrJoin['onTable'], $arrJoin['onField'] );
            }
        }

        return $strJoinStatement;
    }


    protected function createWhereStatement() {

        $strWhereStatement = '';

        if ( !$this->arrQuery['where'] || empty( $this->arrQuery['where'] ) || !is_array( $this->arrQuery['where'] ) ) {

            return $strWhereStatement;
        }

        $strWhereStatement .= ' WHERE';

        foreach ( $this->arrQuery['where'] as $intIndex => $arrQuery ) {

            if ( $intIndex ) $strWhereStatement .= ' AND';

            if ( !empty( $arrQuery[0] ) && is_array( $arrQuery[0] ) ) {

                $strEndSeparator = '';
                $strStartSeparator = '(';
                $intTotal = count( $arrQuery ) - 1;

                foreach ( $arrQuery as $intSubIndex => $arrSubQuery ) {

                    if ( $intSubIndex ) $strStartSeparator = '';
                    if ( $intTotal == $intSubIndex ) $strEndSeparator = ')';

                    $strWhereStatement .= $strStartSeparator . $this->createQueryStatement( $arrSubQuery, ( $intSubIndex ? ' OR' : '' ), $strEndSeparator, $intTotal );
                }

            } else {

                if ( !$arrQuery['operator'] ) continue;

                $strWhereStatement .= $this->createQueryStatement( $arrQuery, '' );
            }
        }

        return $strWhereStatement;
    }


    protected function createQueryStatement( $arrQuery, $strOperator, $strSeparator = '', $intParentTotal = 0 ) {

        $strQuery = '';

        if ( is_bool( $arrQuery['multiple'] ) &&  $arrQuery['multiple'] == true ) {
            
            if ( !empty( $arrQuery['value'] ) && is_array( $arrQuery['value'] ) ) {

                $strQuerySeparator = '(';
                $intTotal = count( $arrQuery['value'] ) - 1;

                foreach ( $arrQuery['value'] as $intIndex => $strValue ) {

                    if ( $intIndex || $intParentTotal ) $strQuerySeparator = '';
                    if ( $intTotal == $intIndex ) $strQuerySeparator = ')';

                    $strQuery .= $this->createQueryStatement([

                        'value' => $strValue,
                        'field' => $arrQuery['field'],
                        'operator' => $arrQuery['operator']

                    ], ( $intIndex || $intParentTotal ? ' OR' : '' ), ( count( $arrQuery['value'] ) > 1 ? $strQuerySeparator : '' ) );
                }

                return $strQuery;
            }
        }

        $this->setValue( $arrQuery['value'], $arrQuery['field'] );
        $strEndSeparator = ( $strSeparator == ')' ? $strSeparator : '' );
        $strStartSeparator = ( $strSeparator == '(' ? $strSeparator : '' );

        $strQuery .= $strStartSeparator . $strOperator . ' ' . call_user_func_array( [ 'SQLQueryBuilder', $arrQuery['operator'] ], [ $arrQuery['field'] ] ) . $strEndSeparator;

        return $strQuery;
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