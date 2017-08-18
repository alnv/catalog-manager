<?php

namespace CatalogManager;

class tl_catalog extends \Backend {


    private $arrCatalogFieldCache = [];
    private $arrDataContainerFields = [];


    public function checkPermission() {

        $objDCAPermission = new DCAPermission();
        $objDCAPermission->checkPermission( 'tl_catalog' , 'catalog', 'catalogp' );
    }


    public function createTableOnSubmit( \DataContainer $dc ) {

        $strTablename = $dc->activeRecord->tablename;

        if ( !$strTablename ) return null;

        $objDatabaseBuilder = new CatalogDatabaseBuilder();
        $objDatabaseBuilder->initialize( $strTablename, $dc->activeRecord->row() );

        if ( $dc->activeRecord->tstamp ) {

            $objDatabaseBuilder->tableCheck();

            return null;
        }

        if ( $this->Database->tableExists( $strTablename ) ) {

            return null;
        }

        $objDatabaseBuilder->createTable();

        /*
        if ( $dc->activeRecord->permissionType ) {

            $this->insertPermissionFields( $dc->activeRecord->tablename, $dc->activeRecord->permissionType );
        }
        */
    }


    public function getCatalogFields( \DataContainer $dc ) {

        if ( !empty( $this->arrCatalogFieldCache ) && is_array( $this->arrCatalogFieldCache ) ) {
            
            return $this->arrCatalogFieldCache;
        }

        $arrReturn = [];
        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ?' )->execute( $dc->activeRecord->id );

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->fieldname ) continue;

            $arrReturn[ $objCatalogFields->fieldname ] = $objCatalogFields->title;
        }

        $this->arrCatalogFieldCache = $arrReturn;

        return $this->arrCatalogFieldCache;
    }


    public function renameTable( $varValue, \DataContainer $dc ) {

        if ( !$varValue || !$dc->activeRecord->tablename || $dc->activeRecord->tablename == $varValue ) {

            return $varValue;
        }

        if ( !$this->Database->tableExists( $varValue ) ) {

            $objDatabaseBuilder = new CatalogDatabaseBuilder();
            $objDatabaseBuilder->initialize( $dc->activeRecord->tablename, $dc->activeRecord->row() );
            $objDatabaseBuilder->renameTable( $varValue );

            /*
            if ( $dc->activeRecord->permissionType ) {

                $this->renamePermissionFields( $dc->activeRecord->tablename, $varValue, $dc->activeRecord->permissionType );
            }
            */
        }

        return $varValue;
    }


    public function dropTableOnDelete( \DataContainer $dc ) {

        $objDatabaseBuilder = new CatalogDatabaseBuilder();
        $objDatabaseBuilder->initialize( $dc->activeRecord->tablename, $dc->activeRecord->row() );
        $objDatabaseBuilder->dropTable();

        // $this->dropPermissionFields( $dc->activeRecord->tablename );
    }

    /*
    protected function dropPermissionFields( $strField ) {

        $objSQLBuilder = new SQLBuilder();

        $objSQLBuilder->dropTableField( 'tl_user' , $strField );
        $objSQLBuilder->dropTableField( 'tl_user' , $strField . 'p' );

        $objSQLBuilder->dropTableField( 'tl_user_group' , $strField );
        $objSQLBuilder->dropTableField( 'tl_user_group' , $strField . 'p' );

        $objSQLBuilder->dropTableField( 'tl_member_group' , $strField );
        $objSQLBuilder->dropTableField( 'tl_member_group' , $strField . 'p' );
    }
    */

    /*
    protected function insertPermissionFields( $strField, $strType ) {

        $objSQLBuilder = new SQLBuilder();

        $objSQLBuilder->alterTableField( 'tl_user', $strField . 'p', 'blob NULL' );
        $objSQLBuilder->alterTableField( 'tl_user_group', $strField . 'p', 'blob NULL' );

        $objSQLBuilder->alterTableField( 'tl_member_group', $strField, 'blob NULL' );
        $objSQLBuilder->alterTableField( 'tl_member_group', $strField . 'p', 'blob NULL' );

        if ( $strType == 'extended' ) {

            $objSQLBuilder->alterTableField( 'tl_user', $strField, 'blob NULL' );
            $objSQLBuilder->alterTableField( 'tl_user_group', $strField, 'blob NULL' );
        }
    }
    */

    /*
    protected function renamePermissionFields( $strOldFieldname, $strNewFieldname, $strType ) {

        $objSQLBuilder = new SQLBuilder();

        $objSQLBuilder->createSQLRenameFieldnameStatement( 'tl_user', $strOldFieldname . 'p', $strNewFieldname . 'p', 'blob NULL' );
        $objSQLBuilder->createSQLRenameFieldnameStatement( 'tl_user_group', $strOldFieldname . 'p', $strNewFieldname . 'p', 'blob NULL' );

        $objSQLBuilder->createSQLRenameFieldnameStatement( 'tl_member_group', $strOldFieldname, $strNewFieldname, 'blob NULL' );
        $objSQLBuilder->createSQLRenameFieldnameStatement( 'tl_member_group', $strOldFieldname . 'p', $strNewFieldname . 'p', 'blob NULL' );

        if ( $strType == 'extended' ) {

            $objSQLBuilder->createSQLRenameFieldnameStatement( 'tl_user', $strOldFieldname, $strNewFieldname, 'blob NULL' );
            $objSQLBuilder->createSQLRenameFieldnameStatement( 'tl_user_group', $strOldFieldname, $strNewFieldname, 'blob NULL' );
        }
    }
    */
    
    public function getPanelLayouts() {

        return [ 'filter', 'sort', 'search', 'limit' ];
    }


    public function getOperations() {

        return [ 'cut', 'copy', 'invisible' ];
    }


    public function getModeTypes ( \DataContainer $dc ) {

        if ( $dc->activeRecord->pTable ) return [ '4' ];

        return [ '0', '1', '2', '4', '5' ];
    }


    public function getFlagTypes() {

        return [ '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12' ];
    }


    public function checkTablename( $varValue ) {

        if ( Toolkit::isCoreTable( $varValue ) ) {

            throw new \Exception('"tl_" prefix is not allowed.');
        }

        return Toolkit::parseConformSQLValue( $varValue );
    }


    public function checkModeTypeRequirements( $varValue, \DataContainer $dc ) {

        if ( $varValue == '4' && !$dc->activeRecord->pTable ) {

            throw new \Exception('this mode required parent table.');
        }

        return $varValue;
    }


    public function getParentDataContainerFields( \DataContainer $dc ) {

        $strPTable = $dc->activeRecord->pTable;

        if ( !$strPTable || !$this->Database->tableExists( $strPTable ) ) return [];

        $objSQLBuilder = new SQLBuilder();
        $arrFields = array_keys( $objSQLBuilder->showColumns( $strPTable ) );

        return $arrFields;
    }

    // @todo improve
    public function getDataContainerFields( \DataContainer $dc ) {

        if ( !empty( $this->arrDataContainerFields ) && is_array( $this->arrDataContainerFields ) ) {

            return $this->arrDataContainerFields;
        }

        $strID = \Input::get('id');
        $this->arrDataContainerFields = [ 'id', 'title', 'alias', 'tstamp' ];
        $arrOperators = Toolkit::deserialize( $dc->activeRecord->operations );

        if ( in_array( 'invisible', $arrOperators ) && ( $this->Database->fieldExists( 'invisible', $dc->activeRecord->tablename ) ) ) {

            $this->arrDataContainerFields[] = 'invisible';
            $this->arrDataContainerFields[] = 'start';
            $this->arrDataContainerFields[] = 'stop';
        }

        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE `pid` = ?' )->execute( $strID );

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->fieldname ) {

                continue;
            }

            $this->arrDataContainerFields[] = $objCatalogFields->fieldname;
        }

        if ( $this->Database->tableExists( $dc->activeRecord->tablename ) ) {

            if ( $this->Database->fieldExists( 'sorting', $dc->activeRecord->tablename ) ) {

                $this->arrDataContainerFields[] = 'sorting';
            }
        }

        return $this->arrDataContainerFields;
    }


    public function getAllCTables( \DataContainer $dc ) {

        $arrReturn = [];
        $objCatalogTables = $this->Database->prepare( 'SELECT `id`, `name`, `tablename`, `pTable` FROM tl_catalog' )->execute();

        while ( $objCatalogTables->next() ) {

            if ( $dc->activeRecord->tablename && $dc->activeRecord->tablename == $objCatalogTables->tablename ) {

                continue;
            }

            
            $arrReturn[] = $objCatalogTables->tablename;
        }

        return $arrReturn;
    }


    public function getAllPTables( \DataContainer $dc ) {

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


    public function checkModeTypeForFormat( $varValue, \DataContainer $dc ) {

        $arrNotAllowedModeTypes = [ '4', '5' ];

        if ( $varValue && in_array( $dc->activeRecord->mode , $arrNotAllowedModeTypes ) ) {

            throw new \Exception('you can not use format in this mode.');
        }

        return $varValue;
    }


    public function checkModeTypeForPTableAndModes( $varValue, \DataContainer $dc ) {

        if ( $varValue && $dc->activeRecord->pTable ) {

            throw new \Exception('you can not generate backend module with parent table.');
        }

        return $varValue;
    }


    public function checkModeTypeForBackendModule( $varValue, \DataContainer $dc ) {

        if ( $varValue && $dc->activeRecord->isBackendModule ) {

            throw new \Exception('you can not use parent table for backend module.');
        }

        return $varValue;
    }


    public function getNavigationAreas() {

        $arrReturn = [];
        $arrModules = $GLOBALS['BE_MOD'] ? $GLOBALS['BE_MOD'] : [];

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

    
    public function getChangeLanguageColumns( \DataContainer $dc ) {

        $strTable = '';
        $arrReturn = [];

        if ( !$dc->activeRecord->languageEntitySource ) {

            return $arrReturn;
        }

        switch ( $dc->activeRecord->languageEntitySource ) {

            case 'parentTable':

                $strTable = $dc->activeRecord->pTable;

                break;

            case 'currentTable':

                $strTable = $dc->activeRecord->tablename;

                break;
        }

        if ( !$strTable ) {

            return $arrReturn;
        }

        if ( $this->Database->tableExists( $strTable ) ) {

            $arrColumns = $this->Database->listFields( $strTable );

            $arrReturn = Toolkit::parseColumns( $arrColumns );
        }

        return $arrReturn;
    }


    public function getInternalCatalogFields() {

        $arrReturn = [];
        $strID = \Input::get('id');
        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE `pid` = ? AND `pagePicker` = ?' )->execute( $strID, '1' );

        if ( !$objCatalogFields->numRows ) return $arrReturn;

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->fieldname ) continue;

            $arrReturn[ $objCatalogFields->fieldname ] = $objCatalogFields->title ? $objCatalogFields->title : $objCatalogFields->fieldname;
        }

        return $arrReturn;
    }


    public function getExternalCatalogFields() {

        $arrReturn = [];
        $strID = \Input::get('id');
        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE `pid` = ? AND `rgxp` = ?' )->execute( $strID, 'url' );

        if ( !$objCatalogFields->numRows ) return $arrReturn;

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->fieldname ) continue;

            $arrReturn[ $objCatalogFields->fieldname ] = $objCatalogFields->title ? $objCatalogFields->title : $objCatalogFields->fieldname;
        }

        return $arrReturn;
    }


    public function getPermissionTypes() {

        return [ 'default', 'extended' ];
    }
}