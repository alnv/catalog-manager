<?php

namespace CatalogManager;

class SQLQueryBuilder extends CatalogController {

    private $strQuery = '';
    private $strTable = '';
    private $arrQuery = [];
    private $arrValues = [];

    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
    }

    public function getQuery( $arrQuery ) {

        $this->arrQuery = $arrQuery;
        $this->strTable = $arrQuery['table'];

        $this->createSelectQuery();

        return $this->strQuery;
    }

    public function execute( $arrQuery ) {

        $this->getQuery( $arrQuery );

        return $this->Database->prepare( $this->strQuery )->execute( $this->arrValues );
    }

    protected function createSelectQuery() {

        $this->strQuery = sprintf( 'SELECT * FROM %s %s %s %s',

            $this->strTable,
            $this->createWhereStatement(),
            $this->createPaginationStatement(),
            $this->createOrderByStatement()
        );
    }

    protected function equal( $strField ) {

        return sprintf( '`%s` = ?', $strField );
    }

    protected function regexp( $strField ) {

        return sprintf( 'LOWER(CAST(`%s` AS CHAR)) REGEXP LOWER(?)', $strField );
    }

    protected function gt( $strField ) {

        return sprintf( 'LOWER(CAST(`%s` AS SIGNED)) > ?', $strField );
    }

    protected function gte( $strField ) {

        return sprintf( 'LOWER(CAST(`%s` AS SIGNED)) >= ?', $strField );
    }

    protected function lt( $strField ) {

        return sprintf( 'LOWER(CAST(`%s` AS SIGNED)) < ?', $strField );
    }

    protected function lte( $strField ) {

        return sprintf( 'LOWER(CAST(`%s` AS SIGNED)) <= ?', $strField );
    }

    protected function contain( $strField ) {

        return sprintf( 'LOWER(`%s`) IN (?)', $strField );
    }

    protected function between( $strField ) {

        return sprintf( 'LOWER(`%s`) BETWEEN ? AND ?', $strField );
    }

    protected function createWhereStatement() {

        $strWhereStatement = '';

        if ( !$this->arrQuery['where'] || empty( $this->arrQuery['where'] ) || !is_array( $this->arrQuery['where'] ) ) {

            return $strWhereStatement;
        }

        $strWhereStatement .= 'WHERE ';

        foreach ( $this->arrQuery['where'] as $intIndex => $arrQueries ) {

            if ( $intIndex )  $strWhereStatement .= ' AND ';

            if ( !empty( $arrQueries[ $intIndex ] ) && is_array( $arrQueries[ $intIndex ] ) ) {

                $intOrIndex = 0;
                $strWhereStatement .= '(';

                foreach ( $arrQueries as $strKey => $varValue ) {

                    if ( $intOrIndex ) $strWhereStatement .= ' OR ';

                    if ( !$varValue['operator'] ) continue;

                    $strWhereStatement .= call_user_func_array( [ 'SQLQueryBuilder', $varValue['operator'] ], [ $varValue['field'] ] );

                    $this->arrValues[] = $varValue['value'];

                    $intOrIndex++;
                }

                $strWhereStatement .= ')';
            }

            else {

                if ( $arrQueries['operator'] ) {

                    $strWhereStatement .= call_user_func_array( [ 'SQLQueryBuilder', $arrQueries['operator'] ], [ $arrQueries['field'] ] );

                    $this->arrValues[] = $arrQueries['value'];
                }
            }
        }

        return $strWhereStatement;
    }

    protected function createPaginationStatement() {

        return '';
    }

    protected function createOrderByStatement() {

        return '';
    }
}