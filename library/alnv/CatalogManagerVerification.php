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
            'lastUpdate' => date( 'd.m.Y H:i',\Date::floorToMinute() )
        ];
    }


    public function verify( $strLicence = '' ) {

        $objRequest = new \Request();
        $arrContaoInstallData = $this->getContaoInstallData();
        
        if ( $strLicence ) $arrContaoInstallData['licence'] = $strLicence;

        $strRequestData = http_build_query( $arrContaoInstallData );
        $objRequest->send( sprintf( 'https://verification-center.alexandernaumov.de/verify?%s', $strRequestData ) );

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
}