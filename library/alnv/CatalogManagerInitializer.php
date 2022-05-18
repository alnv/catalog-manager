<?php

namespace CatalogManager;

class CatalogManagerInitializer {

    protected $objIconGetter;
    protected $arrCatalogs = [];
    protected $arrCoreTables = [];
    protected $arrBackendModules = [];
    protected $objI18nCatalogTranslator;
    protected $arrActiveBackendModules = [];

    public function initialize() {

        $blnInitializeInBackend = TL_MODE == 'BE';
        if (version_compare('4.4', VERSION, '<=')) {
            $objRequest = \System::getContainer()->get('request_stack')->getCurrentRequest();
            if ($objRequest && $objRequest->get('_route') == 'contao_install') {
                $blnInitializeInBackend = false;
            }
        }

        if ($blnInitializeInBackend) {
            \Database::getInstance();
            $this->objIconGetter = new IconGetter();
            $this->objIconGetter->createCatalogManagerDirectories();
            $this->objI18nCatalogTranslator = new I18nCatalogTranslator();
            $this->objI18nCatalogTranslator->initialize();
            $this->setCatalogs();
            $this->setNavigation();
            $this->modifyCoreModules();
            $this->setBackendModules();
            $this->initializeDataContainerArrays();
            \BackendUser::getInstance();
            if (\Config::get('catalogLicence')) {
                unset($GLOBALS['BE_MOD']['catalog-manager-extensions']['support']);
            }
        }

        if (TL_MODE == 'FE') {
            \FrontendUser::getInstance();
            \Database::getInstance();
            $this->setCatalogs();
        }

        if (\Config::get('_isBlocked')) {
            $strMessage = 'Our system detected an unlicensed catalog manager installation on the domain "'. \Environment::get('base') .'". Please enter your valid license or contact your webmaster.';
            \Message::addError($strMessage);
            \System::log($strMessage, 'CATALOG MANAGER VERIFICATION', TL_ERROR);
        }
    }


    protected function setCatalogs() {

        $objDatabase = \Database::getInstance();

        if (!$objDatabase->tableExists('tl_catalog')) return null;

        $objCatalog = $objDatabase->prepare('SELECT * FROM tl_catalog ORDER BY `pTable` DESC, `tablename` ASC')->execute();
        if ($objCatalog->numRows) {
            while ($objCatalog->next()) {
                if (!$objCatalog->tablename) {
                    continue;
                }
                $arrCatalog = Toolkit::parseCatalog($objCatalog->row());
                $strType = Toolkit::isCoreTable($objCatalog->tablename) ? 'arrCoreTables' : 'arrCatalogs';
                $this->{$strType}[$objCatalog->tablename] = $arrCatalog;
                $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$objCatalog->tablename] = $arrCatalog;
            }
        }

        $GLOBALS['TL_CATALOG_MANAGER']['CORE_TABLES'] = array_keys($this->arrCoreTables);
    }


    protected function setNavigation() {

        $arrNavigationAreas = \Config::get('catalogNavigationAreas');
        $arrNavigationAreas = \StringUtil::deserialize($arrNavigationAreas, true);

        if ( !empty( $arrNavigationAreas ) && is_array( $arrNavigationAreas ) ) {

            foreach ( $arrNavigationAreas as $intIndex => $arrNavigationArea ) {

                if ( !Toolkit::isEmpty( $arrNavigationArea['key'] ) ) {

                    $arrNav = [];
                    $arrNav[ $arrNavigationArea['key'] ] = [];
                    array_insert(  $GLOBALS['BE_MOD'], $intIndex, $arrNav );
                    $GLOBALS['TL_LANG']['MOD'][ $arrNavigationArea['key'] ] = $this->objI18nCatalogTranslator->get( 'nav', $arrNavigationArea['key'], [ 'title' => $arrNavigationArea['value'] ] );
                }
            }
        }
    }


    protected function setBackendModules() {

        foreach ( $this->arrCatalogs as $strTablename => $arrCatalog ) {

            if ( $arrCatalog['isBackendModule'] && Toolkit::isEmpty( $arrCatalog['pTable'] ) ) {

                $arrBackendModule = [];
                $intIndex = (int) $arrCatalog['navPosition'];
                $strArea = $arrCatalog['navArea'] ? $arrCatalog['navArea'] : 'system';
                $strModulename = $arrCatalog['modulename'] ? $arrCatalog['modulename'] : $strTablename;
                $arrBackendModule[ $strModulename ] = $this->createBackendModuleDc( $strTablename, $arrCatalog );
                $this->arrActiveBackendModules[] = $strModulename;
                array_insert( $GLOBALS['BE_MOD'][ $strArea ], $intIndex, $arrBackendModule );
                $this->arrBackendModules[ $strModulename ] = $arrBackendModule[ $strModulename ];
                $GLOBALS['TL_LANG']['MOD'][ $strModulename ] = $this->objI18nCatalogTranslator->get( 'module', $strTablename );
            }
        }
    }


    protected function modifyCoreModules() {

        $strActiveModule = \Input::get('do');
        $arrCoreTables = array_keys( $this->arrCoreTables );

        if ( Toolkit::isEmpty( $strActiveModule ) || empty( $arrCoreTables ) ) return null;

        foreach ( $GLOBALS['BE_MOD'] as $strArea => $arrModules ) {

            if ( isset( $arrModules[ $strActiveModule ] ) && is_array( $arrModules[ $strActiveModule ] ) ) {

                $arrBackendModule = [];
                $arrModule = $arrModules[ $strActiveModule ];
                $this->arrActiveBackendModules[] = $strActiveModule;
                $arrModule['tables'] = isset($arrModule['tables']) && is_array( $arrModule['tables'] ) ? $arrModule['tables'] : [];

                foreach ( $arrCoreTables as $strTablename ) {

                    if ( in_array( $strTablename, $arrModule['tables'] ) ) {

                        $arrBackendModule = $this->createBackendModuleDc( $strTablename, $this->arrCoreTables[ $strTablename ] );
                    }
                }

                if ( !empty( $arrBackendModule ) && is_array( $arrBackendModule ) ) {

                    $arrTables = $arrBackendModule['tables'];

                    foreach ( $arrTables as $strTable ) {

                        if ( !in_array( $strTable, $arrModule['tables'] ) ) {

                            $arrModule['tables'][] = $strTable;
                        }
                    }

                    $this->arrBackendModules[ $strActiveModule ] = $arrModule;
                    $GLOBALS['BE_MOD'][ $strArea ][ $strActiveModule ] = $arrModule;
                }
            }
        }
    }


    protected function initializeDataContainerArrays() {

        $strActiveModule = $this->getActiveModule();

        if ( in_array( $strActiveModule, $this->arrActiveBackendModules ) ) {

            $arrModule = $this->arrBackendModules[ $strActiveModule ] ?? [];

            if (isset($arrModule['tables']) && is_array($arrModule['tables'])) {

                foreach ( $arrModule['tables'] as $strTablename ) {

                    if ( Toolkit::isCoreTable( $strTablename ) ) continue;

                    $this->initializeDcByTablename( $strTablename, [], true );
                }

                return null;
            }
        }

        foreach ( $this->arrCatalogs as $strTablename => $arrCatalog ) {

            $this->initializeDcByTablename( $strTablename, $arrCatalog, false );
        }
    }


    protected function initializeDcByTablename( $strTablename, $arrCatalog = [], $blnActive = true ) {

        if ( empty( $arrCatalog ) ) $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTablename ];
        if ( $arrCatalog === null ) return null;

        $objDcBuilder = new DcBuilder( $arrCatalog, $blnActive );
        $objDcBuilder->createDataContainerArray();

        if ( !Toolkit::isEmpty( $arrCatalog['permissionType'] ) ) {

            $GLOBALS['TL_PERMISSIONS'][] = $strTablename . 'p';

            if ( $arrCatalog['permissionType'] == 'extended' ) {

                $GLOBALS['TL_PERMISSIONS'][] = $strTablename;
            }

            $GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'][] = [

                'type' => $arrCatalog['permissionType'],
                'tablename' => $strTablename
            ];
        }
    }


    protected function createBackendModuleDc($strTablename, $arrCatalog) {

        $arrModule = [];
        $arrTables[] = $strTablename;
        $blnAddContentElements = $arrCatalog['addContentElements'] ? true : false;

        foreach ($arrCatalog[ 'cTables' ] as $strTable) {
            $arrTables[] = $strTable;
        }

        if (!empty($arrCatalog['cTables']) && is_array($arrCatalog[ 'cTables' ])) {
            $this->getNestedChildTables($arrTables, $arrCatalog[ 'cTables' ], '');
        }

        if ( $blnAddContentElements || $this->existContentElementInChildrenCatalogs( $arrCatalog[ 'cTables' ] ) ) {

            $arrTables[] = 'tl_content';
        }

        $arrModule['stylesheet'] = 'system/modules/catalog-manager/assets/catalog.css';
        $arrModule['icon'] = $this->objIconGetter->setCatalogIcon( $strTablename );
        $arrModule['name'] = $arrCatalog['name'];
        $arrModule['tables'] = $arrTables;

        if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerModifyBackendModule'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerModifyBackendModule'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['catalogManagerModifyBackendModule'] as $arrCallback ) {

                $objHook = new $arrCallback[0]();
                $objHook->{$arrCallback[1]}( $arrModule, $arrCatalog );
            }
        }

        return $arrModule;
    }


    protected function getActiveModule() {

        $strActiveModule = \Input::get('do');

        if ( !Toolkit::isEmpty( \Input::get('target') ) && $strActiveModule == 'files' ) {

            $arrTarget = explode( '.', \Input::get('target') );

            if ( !empty( $arrTarget ) && is_array( $arrTarget ) ) {

                $strActiveModule = !Toolkit::isEmpty( $arrTarget[0] ) ? $arrTarget[0] : $strActiveModule;
            }
        }

        return $strActiveModule;
    }


    protected function existContentElementInChildrenCatalogs($arrTables) {

        if (!empty($arrTables) && is_array($arrTables)) {
            foreach ($arrTables as $strTable) {
                $arrChildTables = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ]['cTables'] ?? '';
                if ($GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ]['addContentElements'] ?? '') {
                    return true;
                }
                if (!empty($arrChildTables) && is_array($arrChildTables)) {
                    return $this->existContentElementInChildrenCatalogs($arrChildTables);
                }
            }
        }

        return false;
    }


    protected function getNestedChildTables(&$arrTables, $arrChildTables, $strTable = '') {

        if ($strTable) {
            $arrTables[] = $strTable;
        }

        if (!empty($arrChildTables ) && is_array($arrChildTables)) {
            foreach ($arrChildTables as $strChildTable) {
                $arrNestedChildTables = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strChildTable]['cTables'] ?? '';
                if (!empty($arrNestedChildTables ) && is_array( $arrNestedChildTables ) ) {
                    foreach ($arrNestedChildTables as $strNestedChildTable) {
                        $this->getNestedChildTables($arrTables, $arrNestedChildTables, $strNestedChildTable);
                    }
                }
            }
        }
    }
}
