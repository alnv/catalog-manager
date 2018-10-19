<?php

namespace CatalogManager;


class CatalogManagerVerification extends CatalogController {


    protected function getContaoInstallData() {
        
        return [

            'name' => 'catalog-manager',
            'version' => constant('VERSION'),
            'ip' => \Environment::get('server'),
            'domain' => \Environment::get('base'),
            'title' => \Config::get('websiteTitle'),
            'licence' => \Config::get('catalogLicence'),
            'lastUpdate' => date( 'Y.m.d H:i',\Date::floorToMinute() ),
            'catalog_manager_version' => constant('CATALOG_MANAGER_VERSION')
        ];
    }


    public function verify( $strLicence = '' ) {

        $objRequest = new \Request();
        $arrContaoInstallData = $this->getContaoInstallData();
        
        if ( $strLicence ) $arrContaoInstallData['licence'] = $strLicence;

        $strRequestData = http_build_query( $arrContaoInstallData );
        $objRequest->send( sprintf( 'https://verification-center.alexandernaumov.de/verify-catalog-manager?%s', $strRequestData ) );

        if ( !$objRequest->hasError() ) {

            $arrResponse = (array) json_decode( $objRequest->response );

            if ( !empty( $arrResponse ) && is_array( $arrResponse ) ) {

                if ( is_bool( $arrResponse['valid'] ) && $arrResponse['valid'] == true ) {

                    return $arrResponse['valid'];
                }
            }
        }

        return false;
    }


    public function isBlocked() {

        $objRequest = new \Request();
        $arrContaoInstallData = $this->getContaoInstallData();

        $strRequestData = http_build_query( $arrContaoInstallData );
        $objRequest->send( sprintf( 'https://verification-center.alexandernaumov.de/is_blocked?%s', $strRequestData ) );

        if ( !$objRequest->hasError() ) {

            $arrResponse = (array) json_decode( $objRequest->response );

            if ( !empty( $arrResponse ) && is_array( $arrResponse ) ) {

                if ( is_bool( $arrResponse['blocked'] ) && $arrResponse['blocked'] == true ) {

                    return $arrResponse['blocked'];
                }
            }
        }

        return false;
    }


    public function toggleIsBlocked( $strValue ) {

        $objConfig = \Config::getInstance();

        if ( isset( $GLOBALS['TL_CONFIG']['_isBlocked'] ) ) {

            $objConfig->update( '$GLOBALS[\'TL_CONFIG\'][\'_isBlocked\']', $strValue );
        }

        else {

            $objConfig->add( '$GLOBALS[\'TL_CONFIG\'][\'_isBlocked\']', $strValue );
        }

        $objConfig->save();
    }
}