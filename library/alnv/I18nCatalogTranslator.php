<?php

namespace CatalogManager;

class I18nCatalogTranslator {


    public function initialize() {
        
        \Controller::loadLanguageFile( 'catalog_manager', $this->getCurrentLanguageIsoCode() );
    }

    
    public function getCurrentLanguageIsoCode() {

        return $GLOBALS['TL_LANGUAGE'] ? $GLOBALS['TL_LANGUAGE'] : 'en';
    }

    
    public function get( $strType, $strName, $arrOptions = [] ) {
        
        switch ( $strType ) {
            
            case 'module':

                $blnTitle = (bool) ($arrOptions['titleOnly'] ?? false);
                $arrLabels = $GLOBALS['TL_LANG']['catalog_manager']['module'][ $strName ] ?? [];

                if ( !is_array( $arrLabels ) || empty( $arrLabels ) ) {

                    $arrLabels = $this->setCatalogLabels( $strName );
                }

                if ( isset( $arrOptions['postfix'] ) && !Toolkit::isEmpty( $arrOptions['postfix'] ) ) {

                    $arrLabels[0] .= ' ' . $arrOptions['postfix'];
                }

                if ( $blnTitle ) return $arrLabels[0] ? $arrLabels[0] : $strName;

                return $arrLabels;

            case 'field':

                $blnTitle = $arrOptions['titleOnly'] ? true : false;

                if ( $arrOptions['table'] && isset( $GLOBALS['TL_LANG']['catalog_manager'][ $arrOptions['table'] ] ) && isset( $GLOBALS['TL_LANG']['catalog_manager'][ $arrOptions['table'] ][ $strName ] ) ) {

                    $arrLabels = $GLOBALS['TL_LANG']['catalog_manager'][ $arrOptions['table'] ][ $strName ];
                }

                else {

                    $arrLabels = $GLOBALS['TL_LANG']['catalog_manager']['fields'][ $strName ];
                }

                if ( !is_array( $arrLabels ) || empty( $arrLabels ) ) {

                    $strTitle = $arrOptions['title'] ?: '';
                    $strDescription = $arrOptions['description'] ?: '';

                    $arrLabels = [ $strTitle, $strDescription ];
                }

                if ( $blnTitle ) return $arrLabels[0] ? $arrLabels[0] : $strName;

                return $arrLabels;

            case 'option':

                $strOption = $GLOBALS['TL_LANG']['catalog_manager']['options'][$strName];

                if (Toolkit::isEmpty($strOption) && $arrOptions['table']) {
                    $strOption = $GLOBALS['TL_LANG']['catalog_manager']['options'][$arrOptions['table']][$strName];
                }

                if (Toolkit::isEmpty($strOption)) {
                    $strOption = $arrOptions['title'] ?: '';
                }

                if (Toolkit::isEmpty($strOption)) {
                    $strOption = $strName;
                }

                return $strOption;

            case 'legend':

                $strLegend = $GLOBALS['TL_LANG']['catalog_manager']['legends'][ $strName ];

                if ( Toolkit::isEmpty( $strLegend ) ) {

                    $strLegend = $arrOptions['title'] ?: '';
                }

                if ( Toolkit::isEmpty( $strLegend ) ) {

                    $strLegend = $strName;
                }

                return $strLegend;

            case 'nav':

                $strLabel = $GLOBALS['TL_LANG']['MOD'][ $strName ];

                if ( Toolkit::isEmpty( $strLabel ) ) {

                    $strLabel = $arrOptions['title'] ?: '';
                }

                if ( Toolkit::isEmpty( $strLabel ) ) {

                    $strLabel = $strName;
                }

                return $strLabel;
        }

        return '';
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


    protected function setCatalogLabels( $strName ) {

        $strName = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strName ]['name'] ?? '';
        $strDescription = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strName ]['description'] ?? '';

        return [ $strName, $strDescription ];
    }
}
