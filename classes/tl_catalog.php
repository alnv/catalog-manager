<?php

namespace CatalogManager;

class tl_catalog extends \Backend {

    private $arrRequiredTableFields = [

        'title' => "varchar(255) NOT NULL default ''",
        'alias' => "varchar(255) NOT NULL default ''",
        'pid' => "int(10) unsigned NOT NULL default '0'",
        'id' => "int(10) unsigned NOT NULL auto_increment",
        'tstamp' => "int(10) unsigned NOT NULL default '0'",
        'sorting' => "int(10) unsigned NOT NULL default '0'",
    ];

    public function createTableOnSubmit( \DataContainer $dc ) {

        if ( !$dc->id || !$dc->activeRecord->tablename ) {

            return null;
        }

        if ( !$this->Database->tableExists( $dc->activeRecord->tablename ) ) {

            $objSQLBuilder = new SQLBuilder();
            
            if ( $dc->activeRecord->mode !== '4' ) {

                unset( $this->arrRequiredTableFields['sorting'] );
            }

            if ( !$dc->activeRecord->pTable ) {

                unset( $this->arrRequiredTableFields['pid'] );
            }

            $objSQLBuilder->createSQLCreateStatement( $dc->activeRecord->tablename, $this->arrRequiredTableFields );
        }

        else {

            $objSQLBuilder = new SQLBuilder();

            if ( $dc->activeRecord->mode == '4' ) {

                $objSQLBuilder->alterTableField( $dc->activeRecord->tablename , 'sorting' , $this->arrRequiredTableFields['sorting'] );
            }

            else {

                $objSQLBuilder->dropTableField( $dc->activeRecord->tablename , 'sorting' );
            }

            if ( $dc->activeRecord->pTable ) {

                $objSQLBuilder->alterTableField( $dc->activeRecord->tablename , 'pid' , $this->arrRequiredTableFields['pid'] );
            }

            else {

                $objSQLBuilder->dropTableField( $dc->activeRecord->tablename , 'pid' );
            }
        }
    }

    public function dropTableOnDelete( \DataContainer $dc ) {

        $objSQLBuilder = new SQLBuilder();
        $objSQLBuilder->createSQLDropTableStatement( $dc->activeRecord->tablename );
    }

    public function getModeTypes () {

        return [ '0', '1', '2', '3', '4', '5' ];
    }

    public function getFlagTypes() {

        return [ '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12' ];
    }

    public function getDataContainerFields( \DataContainer $dc ) {

        $strID = \Input::get('id');
        $arrDefaultFields = [ 'id', 'title', 'alias' ];
        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ?' )->execute( $strID );

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->fieldname ) {

                continue;
            }

            $arrDefaultFields[] = $objCatalogFields->fieldname;
        }

        if ( $this->Database->fieldExists( 'sorting', $dc->activeRecord->tablename ) ) {

            $arrDefaultFields[] = 'sorting';
        }

        return $arrDefaultFields;
    }

    public function getAllTables( \DataContainer $dc ) {

        $arrReturn = [];
        $objCatalogTables = $this->Database->prepare( 'SELECT `id`, `name`, `tablename` FROM tl_catalog' )->execute();

        while ( $objCatalogTables->next() ) {

            if ( $dc->activeRecord->tablename && $dc->activeRecord->tablename == $objCatalogTables->tablename ) {

                continue;
            }

            $arrReturn[] = $objCatalogTables->tablename;
        }

        return $arrReturn;
    }

    public function getNavigationAreas() {

        $arrModules = $GLOBALS['BE_MOD'] ? $GLOBALS['BE_MOD'] : [];
        $arrReturn = [];

        if ( !is_array( $arrModules ) ) {

            return [];
        }

        foreach ( $arrModules as $strName => $arrModule ) {

            $arrLabel = $GLOBALS['TL_LANG']['MOD'][ $strName ];
            $strModuleName = $strName;

            if ( $arrLabel && is_array( $arrLabel ) ) {

                $strModuleName = $arrLabel[0];
            }

            if ( is_string( $arrLabel ) ) {

                $strModuleName = $arrLabel;
            }

            $arrReturn[ $strName ] = $strModuleName;
        }

        return $arrReturn;
    }

    public function getNavigationPosition() {

        return [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20 ];
    }
}