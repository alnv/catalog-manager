<?php

namespace CatalogManager;

class tl_catalog extends \Backend {


    private $arrCatalogFieldCache = [];


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
        }

        return $varValue;
    }


    public function dropTableOnDelete( \DataContainer $dc ) {

        $objDatabaseBuilder = new CatalogDatabaseBuilder();
        $objDatabaseBuilder->initialize( $dc->activeRecord->tablename, $dc->activeRecord->row() );
        $objDatabaseBuilder->dropTable();
    }


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

        if ( Toolkit::isCoreTable( $varValue ) && !$this->Database->tableExists( $varValue ) ) {

            throw new \Exception( sprintf( 'table "%s" do not exist.', $varValue ) );
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


    public function getDataContainerFields( \DataContainer $dc ) {

        $arrReturn = [];
        $strTablename = $dc->activeRecord->tablename;

        if ( !$strTablename ) return $arrReturn;

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize( $dc->activeRecord->tablename );
        $arrFields = $objFieldBuilder->getCatalogFields( true, null );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !Toolkit::isDcConformField( $arrField ) ) continue;

            if ( in_array( $arrField['type'], [ 'upload' ] ) ) continue;

            $arrReturn[ $strFieldname ] = $arrField['_dcFormat']['label'][0] ? $arrField['_dcFormat']['label'][0] : $strFieldname;
        }

        return $arrReturn;
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