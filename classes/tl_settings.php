<?php

namespace CatalogManager;

class tl_settings extends \Backend {


    public function changeGlobals() {

        if ( \Input::get( 'do' ) && \Input::get( 'do' ) == 'settings' ) {

            $GLOBALS['TL_LANG']['MSC']['ow_key'] = &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['ow_key'];
            $GLOBALS['TL_LANG']['MSC']['ow_value'] = &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['ow_value'];
        }
    }


    public function verifyLicence( $varValue ) {

        if ( \Config::get('catalogLicence') == $varValue ) {

            return $varValue;
        }

        $objCatalogManagerVerification = new CatalogManagerVerification();
        $objCatalogManagerVerification->toggleIsBlocked( $objCatalogManagerVerification->isBlocked() );

        if ( !$objCatalogManagerVerification->verify( $varValue ) ) {

            if ( !$varValue ) return '';

            throw new \Exception( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['invalidKey'] );
        }

        return $varValue;
    }


    public function parseNavigationName( $varValue ) {

        $arrReturn = [];
        $arrValues = \StringUtil::deserialize($varValue, true);
        if (!empty($arrValues) && is_array($arrValues)) {
            foreach ($arrValues as $arrValue) {
                $arrReturn[] = [
                    'key' => \StringUtil::generateAlias($arrValue['key']),
                    'value' => $arrValue['value']
                ];
            }
        }
        $arrReturn = serialize($arrReturn);
        return $arrReturn;
    }
}