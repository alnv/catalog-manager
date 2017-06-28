<?php

namespace CatalogManager;

class I18nCatalogTranslator {


    public function initialize() {
        
        \Controller::loadLanguageFile( 'catalog_manager', $this->getCurrentLanguageIsoCode() );
    }

    
    public function getCurrentLanguageIsoCode() {

        return $GLOBALS['TL_LANGUAGE'] ? $GLOBALS['TL_LANGUAGE'] : 'en';
    }

    
    public function getModuleLabel( $strFieldname, $strAdditionalString = '' ) {

        $arrLabel = $GLOBALS['TL_LANG']['catalog_manager']['module'][ $strFieldname ];

        if ( !isset( $arrLabel ) && empty( $arrLabel ) && !is_array( $arrLabel ) ) {

            $strName = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strFieldname]['name'] ?
                $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strFieldname]['name'] : '';

            $strDescription = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strFieldname]['description'] ?
                $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strFieldname]['description'] : '';

            $arrLabel = [ $strName, $strDescription ];
        }

        if ( isset( $strAdditionalString ) && $strAdditionalString != '' ) {

            $arrLabel[0] .= ' ' . $strAdditionalString;
        }

        return $arrLabel;
    }


    public function getModuleTitle( $strFieldname ) {

        $strLabel = &$GLOBALS['TL_LANG']['catalog_manager']['module'][ $strFieldname ][0];

        if ( !$strLabel ) {

            $strLabel = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strFieldname]['name'] ?
                $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strFieldname]['name'] : '';
        }

        return $strLabel;
    }


    public function getFieldLabel( $strI18nKey, $strTitle = '', $strDescription = '' ) {

        $arrLabel = &$GLOBALS['TL_LANG']['catalog_manager']['fields'][ $strI18nKey ];

        if ( !isset( $arrLabel ) && empty( $arrLabel ) && !is_array( $arrLabel ) ) {

            $arrLabel = [ $strTitle, $strDescription ];
        }

        return $arrLabel;
    }

    
    public function getOptionLabel( $strI18nKey, $strGivenOption = '' ) {

        $strOption = &$GLOBALS['TL_LANG']['catalog_manager']['options'][ $strI18nKey ];

        if ( !$strOption ) return $strGivenOption;

        if ( !$strGivenOption ) return $strI18nKey;

        return $strOption;
    }

    
    public function getLegendLabel( $strI18nKey, $strTitle = '' ) {

        $strLegend = &$GLOBALS['TL_LANG']['catalog_manager']['legends'][ $strI18nKey ];

        if ( !$strLegend ) $strLegend = $strTitle;

        if ( !$strLegend ) $strLegend = $strI18nKey;

        return $strLegend;
    }


    public function getNewLabel() {

        return $GLOBALS['TL_LANG']['catalog_manager']['new'];
    }


    public function getShowLabel() {

        return $GLOBALS['TL_LANG']['catalog_manager']['operations']['show'];
    }


    public function getDeleteConfirmLabel() {

        return $GLOBALS['TL_LANG']['catalog_manager']['deleteConfirm'];
    }
}