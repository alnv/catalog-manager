<?php

namespace CatalogManager;

class CatalogManagerVerification extends CatalogController {


    protected function getContaoInstallData() {

        return [

            'name' => 'catalog_manager',
            'ip' => \Environment::get('ip'),
            'domain' => \Environment::get('base'),
            'title' => \Config::get('websiteTitle'),
            'adminEmail' => \Config::get('adminEmail'),
            'licence' => \Config::get('catalogLicence'),
            'lastUpdate' => date( 'd.m.Y H:i',\Date::floorToMinute() )
        ];
    }


    public function initialize() {

        $arrContaoInstallData = $this->getContaoInstallData();
        
        // @todo send data 2 server
        // @todo handle server result

        return true;
    }


    protected function verifyLicence() {

        return true;
    }
}