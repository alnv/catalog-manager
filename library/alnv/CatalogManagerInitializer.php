<?php

namespace CatalogManager;

class CatalogManagerInitializer {

    
    protected $arrModules = [];
    protected $arrLoadDc = [ 'group', 'mgroup', 'user' ];


    public function initialize() {

        if ( TL_MODE == 'BE' ) {

            \BackendUser::getInstance();
            \Database::getInstance();

            $this->createBackendModules();
            $this->initializeDataContainerArrays();
        }
    }


    protected function initializeDataContainerArrays() {

        $strActiveModule = $this->getActiveModule();

        if ( in_array( $strActiveModule, $this->arrLoadDc ) || $strActiveModule == null ) {

            $arrModules = array_keys( $this->arrModules );

            if ( !empty( $arrModules ) && is_array( $arrModules ) ) {

                foreach ( $arrModules as $strTable ) {

                    if ( Toolkit::isEmpty( $strTable ) || Toolkit::isCoreTable( $strTable ) ) {

                        continue;
                    }

                    $this->loadDataContainerArray( $strTable );
                }
            }

            return null;
        }

        if ( !empty( $this->arrModules[ $strActiveModule ] ) && is_array( $this->arrModules[ $strActiveModule ] ) && isset( $this->arrModules[ $strActiveModule ][ $strActiveModule ] ) ) {

            $arrTables = $this->arrModules[ $strActiveModule ][ $strActiveModule ]['tables'];

            if ( !empty( $arrTables ) && is_array( $arrTables ) ) {

                foreach ( $arrTables as $strTable ) {

                    if ( Toolkit::isEmpty( $strTable ) || Toolkit::isCoreTable( $strTable ) ) {

                        continue;
                    }

                    $this->loadDataContainerArray( $strTable );
                }
            }
        }
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


    protected function loadDataContainerArray( $strTable ) {

        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ];

        if ( empty( $arrCatalog ) ) return null;

        $this->createCatalogManagerDc( $arrCatalog );

        if ( $arrCatalog['permissionType'] ) {

            $this->createPermissions( $arrCatalog['tablename'], $arrCatalog['permissionType'] );
        }
    }


    protected function createBackendModules() {

        $this->createDirectories();
        $strTable = \Input::get( 'table' );
        $objDatabase = \Database::getInstance();

        $objI18nCatalogTranslator = new I18nCatalogTranslator();
        $objI18nCatalogTranslator->initialize();

        if ( !$objDatabase->tableExists( 'tl_catalog' ) ) return null;

        $arrNavigationAreas = \Config::get( 'catalogNavigationAreas' );
        $arrNavigationAreas = deserialize( $arrNavigationAreas );

        if ( !empty( $arrNavigationAreas ) && is_array( $arrNavigationAreas ) ) {

            foreach ( $arrNavigationAreas as $intIndex => $arrNavigationArea ) {

                if ( !Toolkit::isEmpty( $arrNavigationArea['key'] ) ) {

                    $arrNav = [];
                    $arrNav[ $arrNavigationArea['key'] ] = [];
                    array_insert(  $GLOBALS['BE_MOD'], $intIndex, $arrNav );
                    $GLOBALS['TL_LANG']['MOD'][ $arrNavigationArea['key'] ] = $objI18nCatalogTranslator->get( 'nav', $arrNavigationArea['key'], [ 'title' => $arrNavigationArea['value'] ] );
                }
            }
        }

        $objCatalogManagerDB = $objDatabase->prepare( 'SELECT * FROM tl_catalog ORDER BY `pTable` DESC, `tablename` ASC' )->limit( 100 )->execute();

        while ( $objCatalogManagerDB->next() ) {

            $arrCatalog = $objCatalogManagerDB->row();

            if ( !$arrCatalog['tablename'] || Toolkit::isEmpty( $arrCatalog['name'] ) ) continue;
            if ( !$objDatabase->tableExists( $arrCatalog['tablename'] ) ) continue;

            $arrCatalog = Toolkit::parseCatalog( $arrCatalog );
            $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $arrCatalog['tablename'] ] = $arrCatalog;

            if ( Toolkit::isCoreTable( $arrCatalog['tablename'] ) ) {

                $GLOBALS['TL_CATALOG_MANAGER']['CORE_TABLES'][] = $arrCatalog['tablename'];
                $this->modifyBackendModule( $arrCatalog );
                continue;
            }

            $this->arrModules[ $arrCatalog['tablename'] ] = $this->createBackendModule( $arrCatalog );

            if ( $arrCatalog['isBackendModule'] && !$arrCatalog['pTable'] ) {

                $this->setBackendModule( $arrCatalog );
                $GLOBALS['TL_LANG']['MOD'][ $arrCatalog['tablename'] ] = $objI18nCatalogTranslator->get( 'module', $arrCatalog['tablename'] );
            }
        }

        if ( $strTable ) $GLOBALS['TL_LANG']['MOD'][ $strTable ] = $objI18nCatalogTranslator->get( 'module', $strTable, [ 'titleOnly' => true ] );
    }

    
    protected function setBackendModule( $arrCatalog ) {

        $strNavigationArea = $arrCatalog['navArea'] ? $arrCatalog['navArea'] : 'system';
        $strNavigationPosition = $arrCatalog['navPosition'] ? intval( $arrCatalog['navPosition'] ) : 0;

        array_insert( $GLOBALS['BE_MOD'][ $strNavigationArea ], $strNavigationPosition, $this->arrModules[ $arrCatalog['tablename'] ] );
    }


    protected function modifyBackendModule( $arrCatalog ) {

        $strModule = \Input::get('do');

        if ( !$strModule ) return null;

        foreach ( $GLOBALS['BE_MOD'] as $strArea => $arrModules ) {

            if ( isset( $arrModules[ $strModule ] ) && is_array( $arrModules[ $strModule ] ) ) {

                $arrModule = $arrModules[ $strModule ];

                if ( is_array( $arrModule['tables'] ) && in_array( $arrCatalog['tablename'], $arrModule['tables'] ) ) {

                    $arrTables = $this->createBackendModule( $arrCatalog )[ $arrCatalog['tablename'] ]['tables'];

                    foreach ( $arrTables as $strTable ) {

                        if ( !in_array( $strTable, $arrModule['tables'] ) ) {

                            $arrModule['tables'][] = $strTable;
                        }
                    }

                    $this->arrLoadDc[] = $strModule;
                    $GLOBALS['BE_MOD'][ $strArea ][ $strModule ] = $arrModule;
                }
            }
        }
    }


    protected function createPermissions( $strPermissionName, $strType ) {

        $GLOBALS['TL_PERMISSIONS'][] = $strPermissionName . 'p';

        if ( $strType == 'extended' ) $GLOBALS['TL_PERMISSIONS'][] = $strPermissionName;

        $GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'][] = [

            'type' => $strType,
            'tablename' => $strPermissionName
        ];
    }


    protected function createBackendModule( $arrCatalog ) {

        $arrTables = [];
        $arrBackendModule = [];
        $objIconGetter = new IconGetter();
        $arrTables[] = $arrCatalog['tablename'];
        $blnAddContentElements = $arrCatalog['addContentElements'] ? true : false;

        foreach ( $arrCatalog[ 'cTables' ] as $strTablename ) {

            $arrTables[] = $strTablename;
        }

        if ( !empty( $arrCatalog[ 'cTables' ] ) && is_array( $arrCatalog[ 'cTables' ] ) ) {

            $this->getNestedChildTables( $arrTables, $arrCatalog[ 'cTables' ], '' );
        }
        
        if ( $blnAddContentElements || $this->existContentElementInChildrenCatalogs( $arrCatalog[ 'cTables' ] ) ) {

            $arrTables[] = 'tl_content';
        }

        $arrBackendModule[ $arrCatalog['tablename'] ] = [

            'icon' => $objIconGetter->setCatalogIcon( $arrCatalog['tablename'] ),
            'name' => $arrCatalog['name'],
            'tables' => $arrTables
        ];

        return $arrBackendModule;
    }


    protected function createCatalogManagerDc( $arrCatalog ) {

        $objDcBuilder = new DcBuilder( $arrCatalog );
        $objDcBuilder->createDataContainerArray();
    }


    protected function createDirectories() {

        $objIconGetter = new IconGetter();
        $objIconGetter->createCatalogManagerDirectories();
    }


    protected function existContentElementInChildrenCatalogs( $arrTables ) {

        if ( !empty( $arrTables ) && is_array( $arrTables ) ) {

            foreach ( $arrTables as $strTable ) {

                $arrChildTables = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ]['cTables'];

                if ( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ]['addContentElements'] ) {

                    return true;
                }

                if ( !empty( $arrChildTables ) && is_array( $arrChildTables ) ) {

                    return $this->existContentElementInChildrenCatalogs( $arrChildTables );
                }
            }
        }

        return false;
    }


    protected function getNestedChildTables( &$arrTables, $arrChildTables, $strTable = '' ) {

        if ( $strTable ) {

            $arrTables[] = $strTable;
        }

        if ( !empty( $arrChildTables ) && is_array( $arrChildTables ) ) {

            foreach ( $arrChildTables as $strChildTable ) {

                $arrNestedChildTables = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strChildTable ]['cTables'];

                if ( !empty( $arrNestedChildTables ) && is_array( $arrNestedChildTables ) ) {

                    foreach (  $arrNestedChildTables as $strNestedChildTable ) {

                        $this->getNestedChildTables( $arrTables, $arrNestedChildTables, $strNestedChildTable );
                    }
                }
            }
        }
    }
}