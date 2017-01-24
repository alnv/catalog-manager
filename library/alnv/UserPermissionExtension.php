<?php

namespace CatalogManager;

class UserPermissionExtension extends CatalogController {

    public function __construct() {

        parent::__construct();
    }

    public function initialize( $strDCAName ) {

        if ( $strDCAName == 'tl_user' || $strDCAName == 'tl_user_group' ) {

            if ( !empty( $GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'] ) && is_array( $GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'] ) ) {

                foreach ( $GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'] as $strCatalogname ) {

                    $this->createSQLColumns( $strCatalogname, $strDCAName );
                    $this->extendUserAndUserGroupDCA( $strCatalogname, $strDCAName );
                }
            }
        }
    }

    private function extendUserAndUserGroupDCA( $strCatalogname, $strDCAName ) {

        if ( $strDCAName == 'tl_user' ) {

            $GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace( 'fop;', sprintf( 'fop;{%s_legend},%s,%s;', $strCatalogname, $strCatalogname, $strCatalogname . 'p' ), $GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] );
            $GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace( 'fop;', sprintf( 'fop;{%s_legend},%s,%s;', $strCatalogname, $strCatalogname, $strCatalogname . 'p' ), $GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] );
        }

        else {

            $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace( 'fop;', sprintf( 'fop;{%s_legend},%s,%s;', $strCatalogname, $strCatalogname, $strCatalogname . 'p' ), $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] );
        }

        $GLOBALS['TL_DCA'][ $strDCAName ]['fields'][ $strCatalogname ] = [

            'label' => [],
            'inputType' => 'checkbox',
            'foreignKey' => sprintf( '%s.title', $strCatalogname ),

            'eval' => [

                'multiple' => true
            ],

            'exclude' => true,
            'sql' => "blob NULL"
        ];

        $GLOBALS['TL_DCA'][ $strDCAName ]['fields'][ $strCatalogname . 'p' ] = [

            'label' => [],
            'inputType' => 'checkbox',

            'options' => [

                'edit',
                'create',
                'delete'
            ],

            'eval' => [

                'multiple' => true
            ],

            'reference' => [],

            'exclude' => true,
            'sql' => "blob NULL"
        ];
    }

    private function createSQLColumns( $strCatalogname, $strDCAName ) {

        $arrColumnsToCreate = [ $strCatalogname, $strCatalogname . 'p' ];

        $objSQLBuilder = new SQLBuilder();

        foreach ( $arrColumnsToCreate as $strFieldname ) {

            $objSQLBuilder->alterTableField( $strDCAName, $strFieldname, 'blob NULL' );
        }
    }
}