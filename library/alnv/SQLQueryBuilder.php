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

    protected function createSelectQuery() {

        $this->strQuery = sprintf( 'SELECT * FROM %s %s %s %s',

            $this->strTable,
            $this->createWhereStatement(),
            $this->createOrderByStatement(),
            $this->createPaginationStatement()
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
                if ( $intIndex ) $strWhereStatement .= '(';

                foreach ( $arrQueries as $strKey => $varValue ) {

                    if ( $intOrIndex ) $strWhereStatement .= ' OR ';

                    if ( !$varValue['operator'] ) continue;

                    $strWhereStatement .= call_user_func_array( [ 'SQLQueryBuilder', $varValue['operator'] ], [ $varValue['field'] ] );

                    $this->setValue( $varValue['value'] );

                    $intOrIndex++;
                }

                if ( $intIndex ) $strWhereStatement .= ')';
            }

            else {

                if ( $arrQueries['operator'] ) {

                    $strWhereStatement .= call_user_func_array( [ 'SQLQueryBuilder', $arrQueries['operator'] ], [ $arrQueries['field'] ] );

                    $this->setValue( $arrQueries['value'] );
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

        return sprintf( 'LIMIT %s, %s', $strOffset, $strLimit );
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

            $arrOrderByStatements[] = sprintf( '`%s` %s', $arrOrderBy['field'], $arrOrderBy['order'] );
        }

        if ( empty( $arrOrderByStatements ) ) {

            return '';
        }

        return 'ORDER BY ' . implode( ',', $arrOrderByStatements );
    }

    private function setValue( $varValue ) {

        if ( is_array( $varValue ) ) {

            foreach ( $varValue as $strValue ) {

                $this->arrValues[] = $strValue;
            }
        }

        else {

            $this->arrValues[] = $varValue;
        }
    }
}