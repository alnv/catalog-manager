<?php

namespace CatalogManager;

class tl_settings extends \Backend {


    public function verifyLicence( $varValue ) {

        if ( !$varValue ) return '';

        $objCatalogManagerVerification = new CatalogManagerVerification();
        $blnValidLicence = $objCatalogManagerVerification->verify( $varValue, false );
        
        if ( !$blnValidLicence ) {

            throw new \Exception( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['invalidKey'] );
        }

        return $varValue;
    }
}