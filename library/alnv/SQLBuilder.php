<?php

namespace CatalogManager;

class SQLBuilder extends CatalogController {

    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
    }

    public function parseCreateFieldStatement( $strField, $strSQLStatement ) {

        return sprintf( '`%s` %s', $strField, $strSQLStatement );
    }

    public function createSQLCreateStatement( $strTable, $arrFields ) {

        if ( !$strTable ) {

            return null;
        }

        $strFieldStatements = [];

        foreach ( $arrFields as $strField => $strSQLStatement ) {

            $strFieldStatements[] = $this->parseCreateFieldStatement( $strField, $strSQLStatement );
        }

        $strFieldStatements[] = 'PRIMARY KEY  (`id`)';

        $strCreateStatement = sprintf( 'CREATE TABLE IF NOT EXISTS `%s` ( %s ) ENGINE=MyISAM DEFAULT CHARSET=UTF8', $strTable, implode( ',', $strFieldStatements ) );

        $this->Database->prepare( $strCreateStatement )->execute();
    }

    public function createSQLDropTableStatement( $strTable ) {

        if ( !$strTable ) {

            return null;
        }

        $strDropTableStatement = sprintf( 'DROP TABLE %s;', $strTable );

        $this->Database->prepare( $strDropTableStatement )->execute();
    }

    public function createSQLRenameTableStatement( $strTable, $strOldTable ) {

        if ( !$strTable ) {

            return null;
        }

        $strRenameTableStatement = sprintf( 'RENAME TABLE %s TO %s', $strOldTable, $strTable );

        $this->Database->prepare( $strRenameTableStatement )->execute();
    }

    public function createSQLRenameFieldnameStatement( $strTable, $strOldFieldname, $strNewFieldname, $strStatement ) {

        if ( !$strTable ) {

            return null;
        }

        $strRenameTableStatement = sprintf( 'ALTER TABLE %s CHANGE `%s` `%s` %s', $strTable, $strOldFieldname, $strNewFieldname, $strStatement );

        $this->Database->prepare( $strRenameTableStatement )->execute();
    }

    public function alterTableField( $strTable, $strField, $strSQLStatement ) {

        if ( !$strTable || !$strField || !$strSQLStatement ) {

            return null;
        }

        if ( !$this->Database->fieldExists( $strField, $strTable ) ) {

            $strAlterFieldStatement = sprintf( 'ALTER TABLE %s ADD `%s` %s ', $strTable, $strField, $strSQLStatement );

            $this->Database->prepare( $strAlterFieldStatement )->execute();
        }
    }

    public function dropTableField( $strTable, $strField ) {

        if ( !$strTable || !$strField ) {

            return null;
        }

        if ( $this->Database->fieldExists( $strField, $strTable ) ) {

            $strDropFieldStatement = sprintf( 'ALTER TABLE %s DROP COLUMN `%s`', $strTable, $strField );

            $this->Database->prepare( $strDropFieldStatement )->execute();
        }
    }

    public function modifyTableField( $strTable, $strField, $strSQLStatement ) {

        if ( !$strTable || !$strField || !$strSQLStatement ) {

            return null;
        }

        if ( $this->Database->fieldExists( $strField, $strTable ) ) {

            $strAlterFieldStatement = sprintf( 'ALTER TABLE %s MODIFY COLUMN %s %s', $strTable, $strField, $strSQLStatement );

            $this->Database->prepare( $strAlterFieldStatement )->execute();
        }
    }

    public function addIndex( $strTable, $strField, $strIndex ) {

        $strAddIndexStatement = '';

        if ( $strIndex == 'index' ) {

            $strAddIndexStatement = sprintf( 'ALTER TABLE %s ADD KEY `%s` (`%s`)', $strTable, $strField, $strField );
        }

        if ( $strIndex == 'unique' ) {

            $strAddIndexStatement = sprintf( 'ALTER TABLE %s ADD UNIQUE KEY (`%s`)', $strTable, $strField );
        }

        if ( $strAddIndexStatement ) {

            $this->Database->prepare( $strAddIndexStatement )->execute();
        }
    }

    public function dropIndex( $strTable, $strField ) {

        $this->Database->prepare( sprintf( 'ALTER TABLE %s DROP INDEX %s', $strTable, $strField ) )->execute();
    }

    public function showColumns( $strTable ) {

        $arrReturn = [];

        $objColumn = $this->Database->prepare( sprintf( 'SHOW COLUMNS FROM %s', $strTable ) )->execute();

        while ( $objColumn->next() ) {

            $arrReturn[ $objColumn->Field ] = [

                'fieldname' => $objColumn->Field,
                'index' => $this->getSQLKey( $objColumn->Key ),
                'statement' => $objColumn->Type . ' ' . $this->getNullStatement( $objColumn->Null ) . ' ' . $this->getDefaultStatement( $objColumn->Default )
            ];
        }

        return $arrReturn;
    }

    public function updateTableFieldByID( $stID, $strTable, $arrValues ) {

        $arrUpdateSet = [];

        if ( !$stID || !$strTable ) {

            return null;
        }

        foreach ( $arrValues as $strFieldname => $strValue ){

            $arrUpdateSet[] = "`{$strFieldname}` = '{$strValue}'";
        }

        if ( !empty( $arrUpdateSet ) && is_array( $arrUpdateSet ) ) {

            $strUpdateFieldStatement = sprintf( 'UPDATE %s SET %s WHERE id = ?', $strTable, implode( ',', $arrUpdateSet  ) );
            $this->Database->prepare( $strUpdateFieldStatement )->execute( $stID );
        }
    }

    private function getPlaceholders( $arrValues, $strPlaceholder = '?' ) {

        return implode( ', ', array_fill( 0, count( $arrValues ), $strPlaceholder ) );
    }

    private function getSQLKey( $strKey ) {
        
        if ( $strKey == 'UNI' ) {

            return 'unique';
        }

        if ( $strKey == 'MUL' ) {

            return 'index';
        }

        return '';
    }

    private function getNullStatement( $strNull ) {

        if ( !$strNull ) {

            return '';
        }

        return $strNull == 'NO' ? 'NOT NULL' : 'NULL';
    }

    private function getDefaultStatement( $strDefault ) {

        if ( is_null( $strDefault ) ) {

            return '';
        }

        return sprintf( "default '%s'", $strDefault );
    }
}