<?php

namespace CatalogManager;

class i18nCatalogTranslator {

    private $strConfigFile = TL_ROOT . '/' . 'system/config/i18nCatalogManager.yaml';

    public function initialize() {

        \Controller::loadLanguageFile( 'catalog_manager', $this->getCurrentLanguageIsoCode() );

        $this->createI18nCatalogConfigFile();
    }

    public function getCurrentLanguageIsoCode() {

        return $GLOBALS['TL_LANGUAGE'] ? $GLOBALS['TL_LANGUAGE'] : 'en';
    }

    public function getFieldLabel( $strI18nKey, $strTitle = '', $strDescription = '' ) {

        $arrLabel = &$GLOBALS['TL_LANG']['catalog_manager']['fields'][ $strI18nKey ];

        if ( empty( $arrLabel ) && !is_array( $arrLabel ) ) {

            $arrLabel = [ $strTitle, $strDescription ];
        }

        // @todo yaml

        return $arrLabel;
    }

    public function getLegendLabel( $strI18nKey ) {

        $strLegend = &$GLOBALS['TL_LANG']['catalog_manager']['legends'][ $strI18nKey ];

        if ( !$strLegend ) {

            $strLegend = $strI18nKey;
        }

        // @todo yaml

        return $strLegend;
    }

    public function getYamlLanguageFile( $strType, $strI18nKey ) {

        return [];
    }

    private function createI18nCatalogConfigFile() {

        if ( !file_exists( $this->strConfigFile ) ) {

            $objFile = fopen( $this->strConfigFile, 'a' );

            fwrite( $objFile, '' );

            fclose( $objFile );
        }
    }
}