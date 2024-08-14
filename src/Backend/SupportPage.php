<?php

namespace Alnv\CatalogManagerBundle\Backend;

use Contao\BackendTemplate;

class SupportPage
{

    public function generate(): string
    {

        $objTemplate = new BackendTemplate('be_catalog_manager_support');

        return $objTemplate->parse();
    }
}