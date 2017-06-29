<?php

namespace CatalogManager;

class MemberPermissionExtension extends CatalogController {


    public function __construct() {

        parent::__construct();

        $this->import( 'SQLBuilder' );
        $this->import( 'I18nCatalogTranslator' );
    }


    public function initialize( $strDCAName ) {

        if ( $strDCAName == 'tl_member' || $strDCAName == 'tl_member_group' ) {

            $arrCatalogs = array_keys( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] );

            if ( !empty( $arrCatalogs ) && is_array( $arrCatalogs ) ) {

                foreach ( $arrCatalogs as $strCatalogname ) {

                    $this->extendMemberGroupDCA( $strCatalogname );
                }
            }
        }
    }


    protected function extendMemberGroupDCA( $strCatalogname ){

        $arrAccessLabel = $this->I18nCatalogTranslator->getModuleLabel( $strCatalogname );
        $arrPermissionLabel = $this->I18nCatalogTranslator->getModuleLabel( $strCatalogname, $this->getPermissionLabel() );

        $arrAccessLabel[1] = $this->getPermissionInfo(0);
        $arrPermissionLabel[1] = $this->getPermissionInfo(1);

        \Controller::loadLanguageFile( 'tl_member_group' );

        $GLOBALS['TL_LANG']['tl_member_group'][ $strCatalogname . '_legend' ] = $arrAccessLabel[0];

        $GLOBALS['TL_DCA']['tl_member_group']['palettes']['__selector__'][] = $strCatalogname;
        $GLOBALS['TL_DCA']['tl_member_group']['subpalettes'][$strCatalogname] = $strCatalogname . 'p';
        $GLOBALS['TL_DCA']['tl_member_group']['palettes']['default'] = str_replace( 'isAdmin;', sprintf( 'isAdmin;{%s:hide},%s;', $strCatalogname . '_legend', $strCatalogname ), $GLOBALS['TL_DCA']['tl_member_group']['palettes']['default'] );

        $GLOBALS['TL_DCA']['tl_member_group']['fields'][ $strCatalogname ] = [

            'label' => $arrAccessLabel,
            'inputType' => 'checkbox',

            'eval' => [

                'submitOnChange' => true,
            ],

            'exclude' => true,
            'sql' => "blob NULL"
        ];

        $GLOBALS['TL_DCA']['tl_member_group']['fields'][ $strCatalogname . 'p' ] = [

            'label' => $arrPermissionLabel,
            'inputType' => 'checkbox',

            'options' => [

                'create',
                'edit',
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


    protected function createSQLColumns( $strCatalogname ) {

        $arrFields = [ $strCatalogname, $strCatalogname . 'p' ];

        foreach ( $arrFields as $strField ) {

            $strSQLStatement =  $GLOBALS['TL_DCA']['tl_member_group']['fields'][$strField]['sql'];

            if ( !$strSQLStatement ) continue;

            $this->SQLBuilder->alterTableField( 'tl_member_group', $strField, $strSQLStatement );
        }
    }


    protected function getPermissionLabel() {

        if ( isset( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'] ) && is_string( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['permission'] ) ) {

            return $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['permission'];
        }

        return '';
    }


    protected function getPermissionInfo( $intIndex = 0 ) {

        $arrPermissionLabels = [ '', '' ];

        if ( isset( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'] ) && is_array( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'] ) ) {

            $arrPermissionLabels = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['permissionInfo'];
        }

        if ( isset( $arrPermissionLabels[ $intIndex ] ) && is_string( $arrPermissionLabels[ $intIndex ] ) ) {

           return $arrPermissionLabels[ $intIndex ];
        }
        
        return '';
    }
}