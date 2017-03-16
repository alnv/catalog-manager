<?php

namespace CatalogManager;

class tl_settings extends \Backend {


    public function verifyLicence( $varValue ) {

        $objCatalogManagerVerification = new CatalogManagerVerification();
        $blnValidLicence = $objCatalogManagerVerification->verify( $varValue, false );

        if ( !$varValue ) return '';

        if ( !$blnValidLicence ) {

            throw new \Exception( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['invalidKey'] );
        }

        return $varValue;
    }
}