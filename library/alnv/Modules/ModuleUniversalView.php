<?php

namespace CatalogManager;

class ModuleUniversalView extends \Module {

    protected $strTemplate = 'mod_catalog_view';

    public function generate() {

        if ( TL_MODE == 'BE' ) {

            //
        }

        return parent::generate();
    }

    protected function compile() {

        //
    }
}