<?php

namespace CatalogManager;

class tl_catalog extends \Backend {


    public function checkPermission() {

        $objDcPermission = new DcPermission();
        $objDcPermission->checkPermission( 'tl_catalog' , 'catalog', 'catalogp' );
    }


    public function setCoreTableData( \DataContainer $dc ) {

        if ( Toolkit::isCoreTable( $dc->activeRecord->tablename ) && \Input::post( 'tl_loadDataContainer' ) ) {

            $objDCAExtractor = new CatalogDcExtractor();
            $objDCAExtractor->initialize( $dc->activeRecord->tablename );
            $arrContainerData = $objDCAExtractor->convertDataContainerToCatalog();

            if ( !empty( $arrContainerData ) ) $this->Database->prepare( 'UPDATE tl_catalog %s WHERE id = ?' )->set( $arrContainerData )->execute( $dc->activeRecord->id );
        }
    }


    public function checkEditMask( \DataContainer $dc ) {

        if ( Toolkit::isEmpty( $dc->id ) ) return null;

        $objCatalog = $this->Database->prepare( 'SELECT * FROM tl_catalog WHERE `id` = ?' )->limit(1)->execute( $dc->id );

        if ( $objCatalog->type == 'modifier') {

            $GLOBALS['TL_DCA']['tl_catalog']['fields']['tablename']['inputType'] = 'select';
            $GLOBALS['TL_DCA']['tl_catalog']['fields']['tablename']['eval']['chosen'] = true;
            $GLOBALS['TL_DCA']['tl_catalog']['fields']['tablename']['eval']['tl_class'] = 'w50 wizard';
            $GLOBALS['TL_DCA']['tl_catalog']['fields']['tablename']['options_callback'] = [ 'CatalogManager\tl_catalog', 'getCoreTables' ];
            $GLOBALS['TL_DCA']['tl_catalog']['fields']['tablename']['wizard'][] = [ 'CatalogManager\DcCallbacks', 'getCoreTableLoaderButton' ];
        }
    }
    

    public function getCoreTables() {

        return $this->getTables();
    }


    protected function getTables( $arrExclude = [] ) {

        $arrReturn = [];
        $arrTables = $this->Database->listTables();

        foreach ( $arrTables as $strTable ) {

            if ( Toolkit::isCoreTable( $strTable ) && !in_array( $strTable, $arrExclude ) ) {

                $arrReturn[] = $strTable;
            }
        }

        return $arrReturn;
    }


    public function createTableOnSubmit( \DataContainer $dc ) {

        $strTablename = $dc->activeRecord->tablename;

        if ( !$strTablename ) return null;

        $objDatabaseBuilder = new CatalogDatabaseBuilder();
        $objDatabaseBuilder->initialize( $strTablename, $dc->activeRecord->row() );

        if ( $this->Database->tableExists( $strTablename ) ) {

            $objDatabaseBuilder->tableCheck();

            return null;
        }

        $objDatabaseBuilder->createTable();
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


    public function getOperations( \DataContainer $dc ) {

        $arrOperations = Toolkit::$arrOperators;

        if (!$dc->activeRecord->mode) {
            $intIndex = array_search('cut', $arrOperations);
            if ($intIndex !== false) {
                unset($arrOperations[$intIndex]);
            }
            $arrOperations = array_values($arrOperations);
        }

        return $arrOperations;
    }


    public function getModeTypes ( \DataContainer $dc ) {

        $blnDynamicPtable = false;
        $strTablename = $dc->activeRecord->tablename;
        $blnCoreTable = Toolkit::isCoreTable( $strTablename );

        if ( $blnCoreTable ) {

            \Controller::loadDataContainer( $strTablename );

            $blnDynamicPtable = $GLOBALS['TL_DCA'][ $strTablename ]['config']['dynamicPtable'] ?: false;
        }

        if ( $dc->activeRecord->pTable || $blnDynamicPtable ) {

            if ( $blnCoreTable ) {

                return [ '3', '4' ];
            }

            if ( $dc->activeRecord->mode == '3' ) { // backwards compatibility

                return [ '3', '4' ];
            }

            return [ '4' ];
        }

        $arrModes = Toolkit::$arrModeTypes;

        unset( $arrModes[3] );
        unset( $arrModes[4] );

        return $arrModes;
    }


    public function getFlagTypes() {

        return [ '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12' ];
    }


    public function checkTablename( $varValue, \DataContainer $dc ) {

        $strTablename = Toolkit::parseConformSQLValue( $varValue );
        $strValidname = Toolkit::slug( $strTablename, [ 'delimiter' => '_' ] );

        if ( $strValidname != $strTablename && Toolkit::strictMode() ) {

            throw new \Exception( sprintf( 'invalid tablename. Please try with "%s"', $strValidname ) );
        }

        if ( !$this->Database->isUniqueValue( 'tl_catalog', 'tablename', $strTablename, $dc->activeRecord->id ) ) {

            throw new \Exception( sprintf( 'table "%s" already exists in catalog manager.', $strTablename ) );
        }

        if ( $dc->activeRecord->type == 'default' && Toolkit::isCoreTable( $strTablename ) ) {

            throw new \Exception( '"tl_" prefix is not allowed.' );
        }

        if ( Toolkit::isCoreTable( $strTablename ) && !$this->Database->tableExists( $strTablename ) ) {

            throw new \Exception( sprintf( 'table "%s" do not exist.', $strTablename ) );
        }

        return $strTablename;
    }


    public function parseModulename( $varValue, \DataContainer $dc ) {

        if ( Toolkit::isEmpty( $varValue ) && $dc->activeRecord->isBackendModule ) {

            $varValue = $dc->activeRecord->name;
        }

        if ( Toolkit::isEmpty( $varValue ) ) {

            return '';
        }

        return Toolkit::slug( $varValue, [ 'delimiter' => '_' ] );
    }


    public function checkModeTypeRequirements( $varValue, \DataContainer $dc ) {

        $blnDynamicPtable = false;
        $strTablename = $dc->activeRecord->tablename;

        if ( Toolkit::isCoreTable( $strTablename ) ) {

            \Controller::loadDataContainer( $strTablename );

            $blnDynamicPtable = $GLOBALS['TL_DCA'][ $strTablename ]['config']['dynamicPtable'] ?: false;
        }

        if ( in_array( $varValue, [ '3', '4', '6' ] ) && ( Toolkit::isEmpty( $dc->activeRecord->pTable ) && !$blnDynamicPtable ) ) {

            throw new \Exception('this mode required parent table.');
        }

        return $varValue;
    }


    public function getParentDataContainerFields( \DataContainer $dc ) {

        $arrReturn = [];
        $strTablename = $dc->activeRecord->pTable;

        if ( Toolkit::isEmpty( $strTablename ) ) return $arrReturn;

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize( $strTablename );
        $arrFields = $objFieldBuilder->getCatalogFields( true, null );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !Toolkit::isDcConformField( $arrField ) ) continue;

            $arrReturn[ $strFieldname ] = Toolkit::getLabelValue( $arrField['_dcFormat']['label'], $strFieldname );
        }

        return $arrReturn;
    }


    public function getDataContainerFields(\DataContainer $dc) {

        $arrReturn = [];
        $strTablename = $dc->activeRecord->tablename;

        if (Toolkit::isEmpty($strTablename)) return $arrReturn;

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize($strTablename);
        $arrFields = $objFieldBuilder->getCatalogFields(true, null, false, false);

        if ($dc->activeRecord->type == 'modifier' && $dc->field == 'labelFields') {
            \Controller::loadDataContainer($strTablename);
            $arrList = $GLOBALS['TL_DCA'][$strTablename]['list'] ?? [];
            if (is_array($arrList) && isset($arrList['label']) && is_array($arrList['label'])) {
                $arrLabelFields = $arrList['label']['fields'] ?? [];
                if (is_array($arrLabelFields) && !empty($arrLabelFields)) {
                    $arrFieldNames = array_keys($arrFields);
                    $arrDiffFields = array_diff($arrLabelFields, $arrFieldNames);
                    if (is_array($arrDiffFields) && !empty($arrDiffFields)) {
                        foreach ($arrDiffFields as $strName) {
                            $arrReturn[$strName] = $strName . ' - (unknown)';
                        }
                    }
                }
            }
        }

        foreach ($arrFields as $strFieldname => $arrField) {
            if (!Toolkit::isDcConformField($arrField)) continue;
            if (in_array($arrField['type'], ['upload'])) continue;
            $strLabel = Toolkit::getLabelValue($arrField['_dcFormat']['label'], $strFieldname);
            $arrReturn[$strFieldname] = $strLabel ?: $strFieldname;
        }

        return $arrReturn;
    }


    public function getSystemTables( \DataContainer $dc ) {

        $blnCore = $dc->activeRecord->type === 'modifier';
        $arrReturn = $blnCore ? $this->getTables( [ 'tl_content' ] ) : [];
        $objCatalogTables = $this->Database->prepare( 'SELECT `id`, `name`, `tablename` FROM tl_catalog WHERE `tablename` != ?' )->execute( $dc->activeRecord->tablename );

        while ( $objCatalogTables->next() ) {

            if ( !in_array( $objCatalogTables->tablename, $arrReturn ) ) {

                $arrReturn[] = $objCatalogTables->tablename;
            }
        }

        return $arrReturn;
    }
    

    public function checkModeTypeForFormat( $varValue, \DataContainer $dc ) {

        if ( $varValue && $dc->activeRecord->mode == '4' ) {

            return '';
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

        if ( !is_array( $arrModules ) ) return [];

        foreach ( $arrModules as $strName => $arrModule ) {

            $arrLabel = $GLOBALS['TL_LANG']['MOD'][ $strName ];
            $strModuleName = $strName;

            if ( $arrLabel && is_array( $arrLabel ) ) $strModuleName = $arrLabel[0];
            if ( is_string( $arrLabel ) ) $strModuleName = $arrLabel;

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

        if ( !$dc->activeRecord->languageEntitySource ) return $arrReturn;

        switch ( $dc->activeRecord->languageEntitySource ) {

            case 'parentTable':

                $strTable = $dc->activeRecord->pTable;

                break;

            case 'currentTable':

                $strTable = $dc->activeRecord->tablename;

                break;
        }

        if ( !$strTable ) return $arrReturn;

        if ( $this->Database->tableExists( $strTable ) ) {

            $objFieldBuilder = new CatalogFieldBuilder();
            $objFieldBuilder->initialize( $strTable );
            $arrFields = $objFieldBuilder->getCatalogFields( false, null );

            foreach ( $arrFields as $strFieldname => $arrField ) {

                if ( !is_numeric( $strFieldname ) ) {

                    $arrReturn[ $strFieldname ] = $arrField['title'] ?: $strFieldname;
                }
            }
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


    public function getSystemLanguages( \DataContainer $dc ) {

        $strFallback = $dc->activeRecord->fallbackLanguage;
        $arrLanguages = \System::getLanguages();

        if ( $strFallback ) {

            unset( $arrLanguages[ $strFallback ] );
        }

        return $arrLanguages;
    }


    public function getFallbackLanguages() {

        return \System::getLanguages();
    }
}