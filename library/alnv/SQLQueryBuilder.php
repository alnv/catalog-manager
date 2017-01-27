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

        $this->strQuery = sprintf( 'SELECT %s FROM %s %s %s %s %s',

            $this->createSelectionStatement(),
            $this->strTable,
            $this->createJoinStatement(),
            $this->createWhereStatement(),
            $this->createOrderByStatement(),
            $this->createPaginationStatement()
        );
    }

    protected function equal( $strField ) {

        return sprintf( '%s.`%s` = ?', $this->strTable, $strField );
    }

    protected function regexp( $strField ) {

        return sprintf( 'LOWER(CAST(%s.`%s` AS CHAR)) REGEXP LOWER(?)', $this->strTable, $strField );
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

        return sprintf( 'LOWER(%s.`%s`) IN (?)', $this->strTable, $strField );
    }

    protected function between( $strField ) {

        return sprintf( 'LOWER(%s.`%s`) BETWEEN ? AND ?', $this->strTable, $strField );
    }

    protected function createSelectionStatement() {

        $strSelectionStatement = '*';

        if ( !$this->arrQuery['joins'] || empty( $this->arrQuery['joins'] ) || !is_array( $this->arrQuery['joins'] ) ) {

            return $strSelectionStatement;
        }

        $strSelectionStatement = sprintf( '%s.*,', $this->strTable );

        foreach ( $this->arrQuery['joins'] as $arrJoin ) {

            $arrColumnAliases = [];
            $arrForeignColumns = $this->getForeignColumnsByTablename( $arrJoin['onTable'] );

            foreach ( $arrForeignColumns as $strForeignColumn ) {

                $arrColumnAliases[] = sprintf( '%s.%s AS %s', $arrJoin['onTable'], $strForeignColumn, $arrJoin['onTable']. ( ucfirst( $strForeignColumn ) ) );
            }

            $strSelectionStatement .= implode( ',' , $arrColumnAliases );
        }

        return $strSelectionStatement;
    }

    protected function createJoinStatement() {

        $strJoinStatement = '';

        if ( !$this->arrQuery['joins'] || empty( $this->arrQuery['joins'] ) || !is_array( $this->arrQuery['joins'] ) ) {

            return $strJoinStatement;
        }

        foreach ( $this->arrQuery['joins'] as $arrJoin ) {

            if ( !$arrJoin['table'] || !$arrJoin['field'] ) {

                continue;
            }

            if ( !$arrJoin['onTable'] || !$arrJoin['onField'] ) {

                continue;
            }

            $strJoinStatement .= sprintf( 'INNER JOIN %s ON %s.%s = %s.%s', $arrJoin['onTable'], $arrJoin['table'], $arrJoin['field'], $arrJoin['onTable'], $arrJoin['onField'] );
        }

        return $strJoinStatement;
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

            $arrOrderByStatements[] = sprintf( '%s.`%s` %s', $this->strTable, $arrOrderBy['field'], $arrOrderBy['order'] );
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

    private function getForeignColumnsByTablename( $strTable ) {

        if ( !$strTable || !$this->Database->tableExists( $strTable ) ) {

            return [];
        }

        return Toolkit::parseColumns( $this->Database->listFields( $strTable ) );
    }
}