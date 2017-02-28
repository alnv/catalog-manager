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
        $objCatalogManagerDB = $objDatabase->prepare( 'SELECT * FROM tl_catalog ORDER BY name ASC LIMIT 100' )->execute();
        
        $objI18nCatalogTranslator = new I18nCatalogTranslator();
        
        while ( $objCatalogManagerDB->next() ) {

            $arrCatalog = $objCatalogManagerDB->row();

            if ( !$arrCatalog['tablename'] || !$arrCatalog['name'] ) continue;

            $arrCatalog['fields'] = Toolkit::parseStringToArray( $arrCatalog['fields'] );
            $arrCatalog['cTables'] = Toolkit::parseStringToArray( $arrCatalog['cTables'] );
            $arrCatalog['operations'] = Toolkit::parseStringToArray( $arrCatalog['operations'] );
            $arrCatalog['panelLayout'] = Toolkit::parseStringToArray( $arrCatalog['panelLayout'] );
            $arrCatalog['headerFields'] = Toolkit::parseStringToArray( $arrCatalog['headerFields'] );

            $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $arrCatalog['tablename'] ] = $arrCatalog;
            $GLOBALS['TL_LANG']['MOD'][ $arrCatalog['tablename'] ] = $objI18nCatalogTranslator->getModuleLabel( $arrCatalog['tablename'] );

            $this->createCatalogManagerDCA( $arrCatalog );

            if ( !$arrCatalog['isBackendModule'] || $arrCatalog['pTable'] ) continue;

            $this->createBackendModuleWithPermissions( $arrCatalog );
        }
    }

    
    private function createBackendModuleWithPermissions( $arrCatalog ) {

        $strNavigationArea = $arrCatalog['navArea'] ? $arrCatalog['navArea'] : 'system';
        $strNavigationPosition = $arrCatalog['navPosition'] ? intval( $arrCatalog['navPosition'] ) : 0;

        array_insert( $GLOBALS['BE_MOD'][ $strNavigationArea ], $strNavigationPosition, $this->createBackendModule( $arrCatalog ) );

        if ( !$arrCatalog['pTable'] ) {

            $this->createPermissions( $arrCatalog['tablename'] );
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

        if ( $arrCatalog['addContentElements'] ) {

            $arrTables[] = 'tl_content';
        }

        $arrBackendModule[ $arrCatalog['tablename'] ] = [

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