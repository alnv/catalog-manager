<?php

namespace CatalogManager;

class InitializeCatalogManager {

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

        $arrInputFields = [];
        $objDatabase = \Database::getInstance();
        $arrFields = DCABuilder::getDefaultDCAFields( $arrCatalog );
        $objCatalogFieldsDB = $objDatabase->prepare( 'SELECT * FROM tl_catalog_fields WHERE `pid` = ? ORDER BY `sorting`' )->execute( $arrCatalog['id'] );

        while ( $objCatalogFieldsDB->next() ) {

            $arrField = $objCatalogFieldsDB->row();

            $arrInputFields[] = $arrField;
            $arrDCAField = DCABuilder::createDCAField( $arrField );

            if ( is_null( $arrDCAField ) ) {

                continue;
            }

            $arrFields[ $arrField['fieldname'] ] = $arrDCAField;
        }

        $GLOBALS['TL_DCA'][ $arrCatalog['tablename'] ] = [

            'config' => DCABuilder::createConfigDCA( $arrCatalog, $arrInputFields ),

            'list' => [

                'label' => DCABuilder::createLabelDCA( $arrCatalog ),
                'sorting' => DCABuilder::createDCASorting( $arrCatalog ),
                'operations' => DCABuilder::createDCAOperations( $arrCatalog ),
                'global_operations' => DCABuilder::createDCAGlobalOperations( $arrCatalog ),
            ],

            'palettes' => DCABuilder::createDCAPalettes( $arrInputFields ),
            'fields' => $arrFields
        ];
    }
}