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

    private function createBackendModules() {

        $objDatabase = \Database::getInstance();
        $objCatalogManagerDB = $objDatabase->prepare( 'SELECT * FROM tl_catalog' )->execute();
        
        while ( $objCatalogManagerDB->next() ) {

            $arrCatalog = $objCatalogManagerDB->row();
            $strNavigationArea = $arrCatalog['navArea'] ? $arrCatalog['navArea'] : 'system';
            $strNavigationPosition = $arrCatalog['navPosition'] ? intval( $arrCatalog['navPosition'] ) : 0;

            if ( !$arrCatalog['tablename'] || !$arrCatalog['name'] ) {

                continue;
            }

            $arrCatalog['fields'] = Toolkit::parseStringToArray( $arrCatalog['fields'] );
            $arrCatalog['cTables'] = Toolkit::parseStringToArray( $arrCatalog['cTables'] );
            $arrCatalog['panelLayout'] = Toolkit::parseStringToArray( $arrCatalog['panelLayout'] );
            $arrCatalog['headerFields'] = Toolkit::parseStringToArray( $arrCatalog['headerFields'] );

            $this->createCatalogManagerDCA( $arrCatalog );

            if ( !$arrCatalog['isBackendModule'] || $arrCatalog['pTable'] ) {

                continue;
            }

            array_insert( $GLOBALS['BE_MOD'][ $strNavigationArea ], $strNavigationPosition, $this->createBackendModule( $arrCatalog ) );

            if ( !$arrCatalog['pTable'] ) {

                $this->createPermissions( $arrCatalog['tablename'] );
            }
        }
    }

    private function createPermissions( $strPermissionName ) {

        $GLOBALS['TL_PERMISSIONS'][] = $strPermissionName;
        $GLOBALS['TL_PERMISSIONS'][] = $strPermissionName . 'p';
        
        $GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'][] = $strPermissionName;
    }

    private function createBackendModule( $arrCatalog ) {

        $arrTables = [];
        $arrBackendModule = [];
        $arrTables[] = $arrCatalog['tablename'];

        foreach ( $arrCatalog[ 'cTables' ] as $strTablename ) {

            $arrTables[] = $strTablename;
        }

        $arrBackendModule[ $arrCatalog['name'] ] = [

            'name' => $arrCatalog['name'],
            'tables' => $arrTables
        ];

        return $arrBackendModule;
    }

    private function createCatalogManagerDCA( $arrCatalog ) {

        $objDCABuilder = new DCABuilder( $arrCatalog );
        $objDCABuilder->createDCA();
    }
}