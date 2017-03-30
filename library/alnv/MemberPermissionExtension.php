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
                    $this->createSQLColumns($strCatalogname);
                }
            }
        }
    }


    protected function extendMemberGroupDCA( $strCatalogname ){

        $arrLabels = $this->I18nCatalogTranslator->getModuleLabel( $strCatalogname );

        \Controller::loadLanguageFile( 'tl_member_group' );

        $GLOBALS['TL_LANG']['tl_member_group'][ $strCatalogname . '_legend' ] = $arrLabels[0] . ' [' . $strCatalogname . ']';

        $GLOBALS['TL_DCA']['tl_member_group']['palettes']['__selector__'][] = $strCatalogname;
        $GLOBALS['TL_DCA']['tl_member_group']['subpalettes'][$strCatalogname] = $strCatalogname . 'p';
        $GLOBALS['TL_DCA']['tl_member_group']['palettes']['default'] = str_replace( 'isAdmin;', sprintf( 'isAdmin;{%s:hide},%s;', $strCatalogname . '_legend', $strCatalogname ), $GLOBALS['TL_DCA']['tl_member_group']['palettes']['default'] );

        $GLOBALS['TL_DCA']['tl_member_group']['fields'][ $strCatalogname ] = [

            'label' => $arrLabels,
            'inputType' => 'checkbox',

            'eval' => [

                'submitOnChange' => true,
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ];

        $GLOBALS['TL_DCA']['tl_member_group']['fields'][ $strCatalogname . 'p' ] = [

            'label' => $this->I18nCatalogTranslator->getModuleLabel( $strCatalogname, $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['permission'] ),
            'inputType' => 'checkbox',

            'options' => [

                'edit',
                'create',
                'delete',
            ],

            'eval' => [

                'multiple' => true
            ],

            'reference' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'],

            'exclude' => true,
            'sql' => "varchar(512) NOT NULL default ''"
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
}