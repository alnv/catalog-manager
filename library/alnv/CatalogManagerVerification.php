<?php

namespace CatalogManager;

class CatalogManagerVerification extends CatalogController {


    protected function getContaoInstallData() {

        $blnLocale = false;
        $strIpAddress = \Environment::get('ip');
        $arrIpBlocks = $strIpAddress ? explode( '.' , $strIpAddress ) : [];
        
        if ( is_array( $arrIpBlocks ) && isset( $arrIpBlocks[0] ) ) {

            $strFirstIpRange = $arrIpBlocks[0];

            if ( $strFirstIpRange && in_array( $strFirstIpRange, [ '127' ] ) ) {

                $blnLocale = true;
            }
        }
        
        return [

            'locale' => $blnLocale,
            'name' => 'catalog-manager',
            'ip' => \Environment::get('ip'),
            'domain' => \Environment::get('base'),
            'title' => \Config::get('websiteTitle'),
            'adminEmail' => \Config::get('adminEmail'),
            'licence' => \Config::get('catalogLicence'),
            'lastUpdate' => date( 'd.m.Y H:i',\Date::floorToMinute() )
        ];
    }


    public function verify( $strLicence = '', $blnLocale = true ) {

        $objRequest = new \Request();
        $arrContaoInstallData = $this->getContaoInstallData();
        
        if ( $strLicence ) $arrContaoInstallData['licence'] = $strLicence;
        if ( $arrContaoInstallData[ 'locale' ] && $blnLocale ) return true;

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