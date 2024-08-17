<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Config;
use Contao\Date;
use Contao\Environment;
use Symfony\Component\HttpFoundation\Request;

class CatalogManagerVerification extends CatalogController
{

    protected function getContaoInstallData(): array
    {

        return [
            'name' => 'catalog-manager',
            'version' => constant('VERSION'),
            'ip' => Environment::get('server'),
            'domain' => Environment::get('base'),
            'title' => Config::get('websiteTitle'),
            'licence' => Config::get('catalogLicence'),
            'lastUpdate' => date('Y.m.d H:i', Date::floorToMinute()),
            'catalog_manager_version' => constant('CATALOG_MANAGER_VERSION')
        ];
    }

    public function verify($strLicence = ''): bool
    {
        return true;
    }

    public function isBlocked(): bool
    {
        return false;
    }

    public function toggleIsBlocked($strValue): void
    {

    }
}