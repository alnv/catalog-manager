<?php

namespace CatalogManager;

use Contao\Config;
use Contao\Request;
use Contao\System;

class GeoCoding extends CatalogController
{

    private string $strCity;
    private string $strStreet;
    private string $strPostal;
    private string $strCountry;
    private string $strStreetNumber;
    private array $arrGoogleMapsCache = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function getCords($strAddress = '', $strLanguage = 'en', $blnServer = false)
    {

        $arrReturn = ['lat' => '', 'lng' => ''];

        if (Toolkit::isEmpty($strAddress)) {
            $arrAddress = [];
            if ($this->strStreet) {
                if ($this->strStreetNumber) $this->strStreet .= ' ' . $this->strStreetNumber;
                $arrAddress[] = $this->strStreet;
            }

            if ($this->strPostal) $arrAddress[] = $this->strPostal;
            if ($this->strCity) $arrAddress[] = $this->strCity;
            if ($this->strCountry) $arrAddress[] = $this->strCountry;
            $strAddress = \implode(',', $arrAddress);
        }

        if (isset($GLOBALS['TL_HOOKS']['catalogManagerGeoCoords']) && \is_array($GLOBALS['TL_HOOKS']['catalogManagerGeoCoords'])) {
            foreach ($GLOBALS['TL_HOOKS']['catalogManagerGeoCoords'] as $arrCallback) {
                if (\is_array($arrCallback)) {
                    return System::importStatic($arrCallback[0])->{$arrCallback[1]}($arrReturn, $strAddress, $strLanguage, $blnServer, $this);
                }
            }
        }

        $strGoogleIDKey = '';
        $strCacheID = \md5(\urlencode($strAddress));
        $strGoogleID = $blnServer ? Config::get('catalogGoogleMapsServerKey') : Config::get('catalogGoogleMapsClientKey');

        if (\is_array($this->arrGoogleMapsCache[$strCacheID])) {
            return $this->arrGoogleMapsCache[$strCacheID];
        }

        if ($strGoogleID) {
            $strGoogleIDKey = \sprintf('&key=%s', $strGoogleID);
        }

        $strGoogleMapsRequest = sprintf('https://maps.googleapis.com/maps/api/geocode/json?address=%s%s&language=%s&region=%s', \urlencode($strAddress), $strGoogleIDKey, \urlencode($strLanguage), \strlen($strLanguage));

        $objRequest = new Request();
        $objRequest->send($strGoogleMapsRequest);

        if ($objRequest->hasError()) {
            System::log($objRequest->error, __METHOD__, TL_GENERAL);
            return $arrReturn;
        }

        $arrResponse = $objRequest->response ? \json_decode($objRequest->response, true) : [];

        if (isset($arrResponse['error_message']) && $arrResponse['error_message']) {
            System::log($arrResponse['error_message'], '\CatalogManager\GeoCoding\getCords', 'Google Maps');
            return $arrReturn;
        }

        if (!empty($arrResponse) && \is_array($arrResponse)) {

            $arrGeometry = $arrResponse['results'][0]['geometry'] ?? [];
            $arrReturn['lat'] = $arrGeometry['location'] ? preg_replace('/,/', '.', (string)$arrGeometry['location']['lat']) : '';
            $arrReturn['lng'] = $arrGeometry['location'] ? preg_replace('/,/', '.', (string)$arrGeometry['location']['lng']) : '';
            $this->arrGoogleMapsCache[$strCacheID] = $arrReturn;

            return $arrReturn;
        }

        return $arrReturn;
    }

    public function setCity($strCity)
    {
        $this->strCity = $strCity ?: '';
    }

    public function setStreet($strStreet)
    {
        $this->strStreet = $strStreet ?: '';
    }

    public function setStreetNumber($strStreetNumber)
    {
        $this->strStreetNumber = $strStreetNumber ?: '';
    }

    public function setPostal($strPostal)
    {
        $this->strPostal = $strPostal ?: '';
    }

    public function setCountry($strCountry)
    {

        $this->strCountry = $strCountry ?: '';
    }
}