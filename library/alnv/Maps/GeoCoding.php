<?php

namespace CatalogManager;

class GeoCoding extends CatalogController {

    private $strCity;
    private $strStreet;
    private $strPostal;
    private $strCountry;
    private $strStreetNumber;

    private $arrGoogleMapsCache = [];

    public function __construct() {

        parent::__construct();
    }

    public function getCords( $strAddress = '', $strLanguage = 'en', $blnServer = false ) {

        $arrReturn = [ 'lat' => '', 'lng' => '' ];

        if ( !$strAddress ) {

            $strAddress = sprintf( '%s %s %s %s %s', $this->strStreet, $this->strStreetNumber, $this->strPostal, $this->strCity, $this->strCountry );
        }

        $strGoogleIDKey = '';
        $strCacheID = md5( urlencode( $strAddress ) );
        $strGoogleID = $blnServer ? \Config::get('catalogGoogleMapsServerKey') : \Config::get('catalogGoogleMapsClientKey');

        if( is_array( $this->arrGoogleMapsCache[ $strCacheID ] ) ) {

            return $this->arrGoogleMapsCache[ $strCacheID ];
        }

        if ( $strGoogleID ) {

            $strGoogleIDKey = sprintf( '&key=%s', $strGoogleID );
        }

        $strGoogleMapsRequest = sprintf( 'https://maps.googleapis.com/maps/api/geocode/json?address=%s%s&language=%s&region=%s', urlencode( $strAddress ), $strGoogleIDKey, urlencode( $strLanguage ), strlen( $strLanguage ) );

        $objRequest = new \Request();
        $objRequest->send( $strGoogleMapsRequest );

        if ( $objRequest->hasError() ) {

            return $arrReturn;
        }

        $objResponse = $objRequest->response ? json_decode( $objRequest->response, true ) : [];

        if( !empty( $objResponse ) && is_array( $objResponse ) ) {

            $arrGeometry = $objResponse['results'][0]['geometry'];

            $arrReturn['lat'] = $arrGeometry['location'] ? $arrGeometry['location']['lat'] : '';
            $arrReturn['lng'] = $arrGeometry['location'] ? $arrGeometry['location']['lng'] : '';

            $this->arrGoogleMapsCache[ $strCacheID ] = $arrReturn;

            return $arrReturn;
        }

        return $arrReturn;
    }

    public function setCity( $strCity ) {

        $this->strCity = $strCity ? $strCity : '';
    }

    public function setStreet( $strStreet ) {

        $this->strStreet = $strStreet ? $strStreet : '';
    }

    public function setStreetNumber( $strStreetNumber ) {

        $this->strStreetNumber = $strStreetNumber ? $strStreetNumber : '';
    }

    public function setPostal( $strPostal ) {

        $this->strPostal = $strPostal ? $strPostal : '';
    }

    public function setCountry( $strCountry ) {

        $this->strCountry = $strCountry ? $strCountry : '';
    }
}