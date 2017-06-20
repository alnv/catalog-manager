<?php

namespace CatalogManager;

class tl_catalog extends \Backend {

    private $arrCatalogFieldCache = [];
    private $arrDataContainerFields = [];
    private $arrCreateSortingFieldOn = [ '4', '5' ];


    private $arrRequiredTableFields = [

        'stop' => "varchar(16) NOT NULL default ''",
        'start' => "varchar(16) NOT NULL default ''",
        'invisible' => "char(1) NOT NULL default ''",
        'title' => "varchar(255) NOT NULL default ''",
        'alias' => "varchar(255) NOT NULL default ''",
        'pid' => "int(10) unsigned NOT NULL default '0'",
        'id' => "int(10) unsigned NOT NULL auto_increment",
        'tstamp' => "int(10) unsigned NOT NULL default '0'",
        'sorting' => "int(10) unsigned NOT NULL default '0'"
    ];


    public function checkPermission() {

        $objDCAPermission = new DCAPermission();
        $objDCAPermission->checkPermission( 'tl_catalog' , 'catalog', 'catalogp' );
    }


    public function createTableOnSubmit( \DataContainer $dc ) {

        $blnVisibleField = false;

        if ( !$dc->activeRecord->tablename ) {

            return null;
        }

        if ( $dc->activeRecord->operations ) {

            $arrOperations = deserialize( $dc->activeRecord->operations );

            if ( !empty( $arrOperations ) && is_array( $arrOperations ) ) {

                $blnVisibleField = in_array( 'invisible' , $arrOperations );
            }
        }

        if ( !$this->Database->tableExists( $dc->activeRecord->tablename ) ) {

            $objSQLBuilder = new SQLBuilder();

            if ( in_array( $dc->activeRecord->mode, [ '0' ] ) ) {

                unset( $this->arrRequiredTableFields['sorting'] );
            }

            if ( !$dc->activeRecord->pTable || !in_array( $dc->activeRecord->mode, $this->arrCreateSortingFieldOn ) ) {

                unset( $this->arrRequiredTableFields['pid'] );
            }

            if ( !$blnVisibleField ) {

                unset( $this->arrRequiredTableFields['invisible'] );
                unset( $this->arrRequiredTableFields['start'] );
                unset( $this->arrRequiredTableFields['stop'] );
            }

            $objSQLBuilder->createSQLCreateStatement( $dc->activeRecord->tablename, $this->arrRequiredTableFields );
        }

        else {

            $objSQLBuilder = new SQLBuilder();

            if ( !in_array( $dc->activeRecord->mode, [ '0' ] ) ) {

                $objSQLBuilder->alterTableField( $dc->activeRecord->tablename , 'sorting' , $this->arrRequiredTableFields['sorting'] );
            }

            if ( $dc->activeRecord->pTable || in_array( $dc->activeRecord->mode, $this->arrCreateSortingFieldOn ) ) {

                $objSQLBuilder->alterTableField( $dc->activeRecord->tablename , 'pid' , $this->arrRequiredTableFields['pid'] );
            }

            if ( $blnVisibleField ) {

                $objSQLBuilder->alterTableField( $dc->activeRecord->tablename , 'stop' , $this->arrRequiredTableFields['stop'] );
                $objSQLBuilder->alterTableField( $dc->activeRecord->tablename , 'start' , $this->arrRequiredTableFields['start'] );
                $objSQLBuilder->alterTableField( $dc->activeRecord->tablename , 'invisible' , $this->arrRequiredTableFields['invisible'] );
            }
        }

        if ( $dc->activeRecord->isBackendModule && !$dc->activeRecord->pTable ) {

            $this->insertPermissionFields( $dc->activeRecord->tablename );
        }
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

            $objSQLBuilder = new SQLBuilder();
            $objSQLBuilder->createSQLRenameTableStatement( $varValue, $dc->activeRecord->tablename );
            $this->renameAllTableDependencies( $dc->id, $varValue, $dc->activeRecord->tablename );
            $this->renamePermissionFields( $varValue, $dc->activeRecord->tablename );
        }

        return $varValue;
    }


    public function dropTableOnDelete( \DataContainer $dc ) {

        $objSQLBuilder = new SQLBuilder();
        $objSQLBuilder->createSQLDropTableStatement( $dc->activeRecord->tablename );

        $this->dropPermissionFields( $dc->activeRecord->tablename );
    }


    protected function dropPermissionFields( $strField ) {

        $objSQLBuilder = new SQLBuilder();

        $objSQLBuilder->dropTableField( 'tl_user' , $strField );
        $objSQLBuilder->dropTableField( 'tl_user' , $strField . 'p' );

        $objSQLBuilder->dropTableField( 'tl_user_group' , $strField );
        $objSQLBuilder->dropTableField( 'tl_user_group' , $strField . 'p' );

        $objSQLBuilder->dropTableField( 'tl_member_group' , $strField );
        $objSQLBuilder->dropTableField( 'tl_member_group' , $strField . 'p' );
    }


    protected function insertPermissionFields( $strField ) {

        $objSQLBuilder = new SQLBuilder();

        $objSQLBuilder->alterTableField( 'tl_user', $strField, 'blob NULL' );
        $objSQLBuilder->alterTableField( 'tl_user', $strField . 'p', 'blob NULL' );

        $objSQLBuilder->alterTableField( 'tl_user_group', $strField, 'blob NULL' );
        $objSQLBuilder->alterTableField( 'tl_user_group', $strField . 'p', 'blob NULL' );

        $objSQLBuilder->alterTableField( 'tl_member_group', $strField, 'blob NULL' );
        $objSQLBuilder->alterTableField( 'tl_member_group', $strField . 'p', 'blob NULL' );
    }


    protected function renamePermissionFields( $strOldFieldname, $strNewFieldname ) {

        $objSQLBuilder = new SQLBuilder();

        $objSQLBuilder->createSQLRenameFieldnameStatement( 'tl_user', $strNewFieldname, $strOldFieldname, 'blob NULL' );
        $objSQLBuilder->createSQLRenameFieldnameStatement( 'tl_user', $strNewFieldname . 'p', $strOldFieldname . 'p', 'blob NULL' );

        $objSQLBuilder->createSQLRenameFieldnameStatement( 'tl_user_group', $strNewFieldname, $strOldFieldname, 'blob NULL' );
        $objSQLBuilder->createSQLRenameFieldnameStatement( 'tl_user_group', $strNewFieldname . 'p', $strOldFieldname . 'p', 'blob NULL' );

        $objSQLBuilder->createSQLRenameFieldnameStatement( 'tl_member_group', $strNewFieldname, $strOldFieldname, 'blob NULL' );
        $objSQLBuilder->createSQLRenameFieldnameStatement( 'tl_member_group', $strNewFieldname . 'p', $strOldFieldname . 'p', 'blob NULL' );
    }


    public function renameAllTableDependencies( $strID, $strTable, $strOldTable ) {

        $objSQLBuilder = new SQLBuilder();
        $objCatalogDb = $this->Database->prepare('SELECT * FROM tl_catalog WHERE id != ?')->execute( $strID );

        while ( $objCatalogDb->next() ) {

            $arrItem = $objCatalogDb->row();

            if ( $arrItem['cTables'] ) {

                $arrCTables = deserialize( $arrItem['cTables'] );

                foreach ( $arrCTables as $intIndex => $strCTable ) {

                    if ( $strCTable == $strOldTable ) {

                        $arrCTables[ $intIndex ] = $strTable;
                    }
                }

                $arrItem['cTables'] = serialize( $arrCTables );
            }

            if ( $arrItem['pTable'] ) {

                if ( $arrItem['pTable'] == $strOldTable ) {

                    $arrItem['pTable'] = $strTable;
                }
            }

            $objSQLBuilder->updateTableFieldByID( $arrItem['id'], 'tl_catalog', $arrItem );
        }
    }


    public function getPanelLayouts() {

        return [ 'filter', 'search', 'limit', 'sort' ];
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


    public function getDataContainerFields( \DataContainer $dc ) {

        if ( !empty( $this->arrDataContainerFields ) && is_array( $this->arrDataContainerFields ) ) {

            return $this->arrDataContainerFields;
        }

        $strID = \Input::get('id');
        $this->arrDataContainerFields = [ 'id', 'title', 'alias', 'tstamp' ];
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
}