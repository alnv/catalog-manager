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
            $strNavigationPosition = $arrCatalog['navPosition'] ? intval( $arrCatalog['navPosition'] ) : 0;

            if ( !$arrCatalog['tablename'] || !$arrCatalog['name'] ) {

                continue;
            }

            $arrCatalog['fields'] = Toolkit::parseStringToArray( $arrCatalog['fields'] );
            $arrCatalog['cTables'] = Toolkit::parseStringToArray( $arrCatalog['cTables'] );
            $arrCatalog['headerFields'] = Toolkit::parseStringToArray( $arrCatalog['headerFields'] );

            $this->createCatalogManagerDCA( $arrCatalog );

            if ( !$arrCatalog['isBackendModule'] ) {

                continue;
            }

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
        $objCatalogFieldsDB = $objDatabase->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ? ORDER BY sorting' )->execute( $arrCatalog['id'] );

        $arrFields = DCABuilder::getDefaultDCAFields( $arrCatalog );

        while ( $objCatalogFieldsDB->next() ) {

            $arrField = $objCatalogFieldsDB->row();
            $arrDCAField = DCABuilder::createDCAField( $arrField );

            if ( is_null( $arrDCAField ) ) {

                continue;
            }

            $arrFields[ $arrField['fieldname'] ] = $arrDCAField;
        }

        $objCatalogFieldsDB->reset();

        $GLOBALS['TL_DCA'][ $arrCatalog['tablename'] ] = [

            'config' => DCABuilder::createConfigDCA( $arrCatalog ),

            'list' => [

                'label' => DCABuilder::createLabelDCA( $arrCatalog ),

                'sorting' => DCABuilder::createDCASorting( $arrCatalog ),

                'operations' => DCABuilder::createDCAOperations( $arrCatalog ),
            ],

            'palettes' => DCABuilder::createDCAPalettes( $objCatalogFieldsDB ),

            'fields' => $arrFields
        ];
    }
}