<?php

namespace CatalogManager;

class UserPermissionExtension extends CatalogController {


    public function __construct() {

        parent::__construct();

        $this->import( 'SQLBuilder' );
        $this->import( 'I18nCatalogTranslator' );
    }


    public function initialize( $strDCAName ) {

        if ( $strDCAName == 'tl_user' || $strDCAName == 'tl_user_group' ) {

            if ( !empty( $GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'] ) && is_array( $GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'] ) ) {

                foreach ( $GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'] as $arrCatalog ) {

                    $this->extendUserAndUserGroupDCA( $arrCatalog['tablename'], $strDCAName, $arrCatalog['type'] );
                }
            }
        }
    }


    protected function extendUserAndUserGroupDCA( $strCatalogname, $strDCAName, $strType ) {

        $arrLabels = $this->I18nCatalogTranslator->getModuleLabel( $strCatalogname );

        if ( $strDCAName == 'tl_user' ) {

            $GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace( 'fop;', sprintf( 'fop;{%s},%s,%s;', $arrLabels[0], ( $strType == 'extended' ? $strCatalogname : '' ), $strCatalogname . 'p' ), $GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] );
            $GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace( 'fop;', sprintf( 'fop;{%s},%s,%s;', $arrLabels[0], ( $strType == 'extended' ? $strCatalogname : '' ), $strCatalogname . 'p' ), $GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] );
        }

        else {

            $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace( 'fop;', sprintf( 'fop;{%s},%s,%s;', $arrLabels[0], ( $strType == 'extended' ? $strCatalogname : '' ), $strCatalogname . 'p' ), $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] );
        }

        if ( $strType == 'extended' ) {

            $GLOBALS['TL_DCA'][ $strDCAName ]['fields'][ $strCatalogname ] = [

                'label' => $arrLabels,
                'inputType' => 'checkbox',
                'foreignKey' => sprintf( '%s.title', $strCatalogname ),

                'eval' => [

                    'multiple' => true
                ],

                'exclude' => true,
                'sql' => "blob NULL"
            ];
        }
        
        $GLOBALS['TL_DCA'][ $strDCAName ]['fields'][ $strCatalogname . 'p' ] = [

            'label' => $this->I18nCatalogTranslator->getModuleLabel( $strCatalogname, $this->getPermissionLabel() ),
            'inputType' => 'checkbox',

            'options' => [

                'edit',
                'create',
                'delete'
            ],

            'eval' => [

                'multiple' => true
            ],

            'reference' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'],

            'exclude' => true,
            'sql' => "blob NULL"
        ];
    }


    protected function getPermissionLabel() {

        if ( isset( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'] ) && is_array( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'] ) ) {

            return $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['permission'];
        }

        return '';
    }
}