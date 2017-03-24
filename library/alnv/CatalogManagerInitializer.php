<?php

namespace CatalogManager;

class CatalogManagerInitializer {

    
    public function initialize() {

        if ( TL_MODE == 'BE' ) {

            \BackendUser::getInstance();
            \Database::getInstance();

            $this->createBackendModules();
        }
    }


    protected function createBackendModules() {

        $this->createDirectories();
        $objDatabase = \Database::getInstance();

        if ( !$objDatabase->tableExists( 'tl_catalog' ) ) return null;

        $objCatalogManagerDB = $objDatabase->prepare( 'SELECT * FROM tl_catalog ORDER BY name ASC' )->limit(100)->execute();

        while ( $objCatalogManagerDB->next() ) {

            $arrCatalog = $objCatalogManagerDB->row();

            if ( !$arrCatalog['tablename'] || !$arrCatalog['name'] ) continue;

            $arrCatalog['fields'] = Toolkit::parseStringToArray( $arrCatalog['fields'] );
            $arrCatalog['cTables'] = Toolkit::parseStringToArray( $arrCatalog['cTables'] );
            $arrCatalog['operations'] = Toolkit::parseStringToArray( $arrCatalog['operations'] );
            $arrCatalog['panelLayout'] = Toolkit::parseStringToArray( $arrCatalog['panelLayout'] );
            $arrCatalog['headerFields'] = Toolkit::parseStringToArray( $arrCatalog['headerFields'] );

            $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $arrCatalog['tablename'] ] = $arrCatalog;

            $this->createCatalogManagerDCA( $arrCatalog );

            if ( !$arrCatalog['isBackendModule'] || $arrCatalog['pTable'] ) continue;

            $this->createBackendModuleWithPermissions( $arrCatalog );
        }
    }

    
    protected function createBackendModuleWithPermissions( $arrCatalog ) {

        $strNavigationArea = $arrCatalog['navArea'] ? $arrCatalog['navArea'] : 'system';
        $strNavigationPosition = $arrCatalog['navPosition'] ? intval( $arrCatalog['navPosition'] ) : 0;

        array_insert( $GLOBALS['BE_MOD'][ $strNavigationArea ], $strNavigationPosition, $this->createBackendModule( $arrCatalog ) );

        if ( !$arrCatalog['pTable'] ) {

            $this->createPermissions( $arrCatalog['tablename'] );
        }
    }


    protected function createPermissions( $strPermissionName ) {

        $GLOBALS['TL_PERMISSIONS'][] = $strPermissionName;
        $GLOBALS['TL_PERMISSIONS'][] = $strPermissionName . 'p';
        $GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'][] = $strPermissionName;
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


    protected function createCatalogManagerDCA( $arrCatalog ) {

        $objDCABuilder = new DCABuilder( $arrCatalog );
        $objDCABuilder->createDCA();
    }


    protected function createDirectories() {

        $objIconGetter = new IconGetter();
        $objIconGetter->createCatalogManagerDirectories();
    }


    protected function existContentElementInChildrenCatalogs( $arrTables ) {

        if ( !empty( $arrTables ) && is_array( $arrTables ) ) {

            foreach ( $arrTables as $strTable ) {

                if ( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable]['addContentElements'] ) {

                    return true;
                }
            }
        }

        return false;
    }
}