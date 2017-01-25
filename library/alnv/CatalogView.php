<?php

namespace CatalogManager;

class CatalogView extends CatalogController {

    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
    }
}