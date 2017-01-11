<?php

namespace CatalogManager;

class InitializeSystem {

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
            $strNavigationPosition = $arrCatalog['navPlace'] ? intval( $arrCatalog['navPlace'] ) : 0;

            if ( !$arrCatalog['tablename'] || !$arrCatalog['name'] ) {

                continue;
            }

            $arrCatalog['fields'] = Toolkit::parseStringToArray( $arrCatalog['fields'] );
            $arrCatalog['cTables'] = Toolkit::parseStringToArray( $arrCatalog['cTables'] );
            $arrCatalog['headerFields'] = Toolkit::parseStringToArray( $arrCatalog['headerFields'] );

            $this->createCatalogManagerDCA( $arrCatalog );

            array_insert( $GLOBALS['BE_MOD'][ $strNavigationArea ], $strNavigationPosition, $this->createBackendModule( $arrCatalog ) );
        }
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

        $objDatabase = \Database::getInstance();
        $objCatalogFieldsDB = $objDatabase->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ?' )->execute( $arrCatalog['id'] );

        while ( $objCatalogFieldsDB->next() ) {

            $arrField = $objCatalogFieldsDB->row();
        }
    }
}