<?php

namespace CatalogManager;

class i18nCatalogTranslator {

    public function initialize() {

        \Controller::loadLanguageFile( 'catalog_manager', $this->getCurrentLanguageIsoCode() );
    }

    public function getCurrentLanguageIsoCode() {

        return $GLOBALS['TL_LANGUAGE'] ? $GLOBALS['TL_LANGUAGE'] : 'en';
    }

    public function getFieldLabel( $strI18nKey, $strTitle = '', $strDescription = '' ) {

        return [];
    }
}