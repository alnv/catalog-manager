<?php

namespace CatalogManager;


class SupportPage {


    public function generate() {

        $objTemplate = new \BackendTemplate( 'be_catalog_manager_support' );

        return $objTemplate->parse();
    }
}