<?php

namespace CatalogManager;

class SQLBuilder extends \Backend {

    public function parseCreateFieldStatement( $strField, $strSQLStatement ) {

        return sprintf( '`%s` %s', $strField, $strSQLStatement );
    }

    public function createSQLCreateStatement( $strTable, $arrFields ) {

        $strFieldStatements = [];

        foreach ( $arrFields as $strField => $strSQLStatement ) {

            $strFieldStatements[] = $this->parseCreateFieldStatement( $strField, $strSQLStatement );
        }

        $strFieldStatements[] = 'PRIMARY KEY  (`id`)';

        $strCreateStatement = sprintf( 'CREATE TABLE `%s` ( %s ) ENGINE=MyISAM DEFAULT CHARSET=UTF8', $strTable, implode( ',', $strFieldStatements ) );

        $this->Database->prepare( $strCreateStatement )->execute();
    }

    public function createSQLDropTableStatement( $strTable ) {

        $strDropTableStatement = sprintf( 'DROP TABLE %s;', $strTable );

        $this->Database->prepare( $strDropTableStatement )->execute();
    }

    public function alterTableField( $strTable, $strField, $strSQLStatement ) {

        if ( !$this->Database->fieldExists( $strField, $strTable ) ) {

            $strAlterFieldStatement = sprintf( 'ALTER TABLE %s ADD `%s` %s ', $strTable, $strField, $strSQLStatement );

            $this->Database->prepare( $strAlterFieldStatement )->execute();
        }
    }

    public function dropTableField( $strTable, $strField ) {

        if ( $this->Database->fieldExists( $strField, $strTable ) ) {

            $strDropFieldStatement = sprintf( 'ALTER TABLE %s DROP COLUMN `%s`', $strTable, $strField );

            $this->Database->prepare( $strDropFieldStatement )->execute();
        }
    }
}