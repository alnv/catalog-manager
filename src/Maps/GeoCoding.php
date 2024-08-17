<?php

namespace Alnv\CatalogManagerBundle\Maps;

use Alnv\CatalogManagerBundle\CatalogController;
use Alnv\CatalogManagerBundle\Toolkit;
use Alnv\ContaoGeoCodingBundle\Library\GeoCoding as GeoCodingBundle;

class GeoCoding extends CatalogController
{

    private string $strCity;

    private string $strStreet;

    private string $strPostal;

    private string $strCountry;

    private string $strStreetNumber;

    public function __construct()
    {
        parent::__construct();
    }

    public function getCords($strAddress = '', $strLanguage = 'en', $blnServer = false): array
    {

        if (Toolkit::isEmpty($strAddress)) {

            $arrAddress = [];

            if ($this->strStreet) {
                if ($this->strStreetNumber) $this->strStreet .= ' ' . $this->strStreetNumber;
                $arrAddress[] = $this->strStreet;
            }

            if ($this->strPostal) $arrAddress[] = $this->strPostal;
            if ($this->strCity) $arrAddress[] = $this->strCity;
            if ($this->strCountry) $arrAddress[] = $this->strCountry;

            $strAddress = implode(',', $arrAddress);
        }

        $arrGeoCodingData = (new GeoCodingBundle())->getGeoCodingByAddress('google-geocoding', $strAddress, $strLanguage);
        $arrReturn['lat'] = $arrGeoCodingData['latitude'] ?? '';
        $arrReturn['lng'] = $arrGeoCodingData['longitude'] ?? '';

        return $arrReturn;
    }

    public function setCity($strCity): void
    {
        $this->strCity = $strCity ?: '';
    }

    public function setStreet($strStreet): void
    {
        $this->strStreet = $strStreet ?: '';
    }

    public function setStreetNumber($strStreetNumber): void
    {
        $this->strStreetNumber = $strStreetNumber ?: '';
    }

    public function setPostal($strPostal): void
    {
        $this->strPostal = $strPostal ?: '';
    }

    public function setCountry($strCountry): void
    {
        $this->strCountry = $strCountry ?: '';
    }
}