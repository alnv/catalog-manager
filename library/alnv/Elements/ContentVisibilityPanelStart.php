<?php

namespace CatalogManager;

class ContentVisibilityPanelStart extends \ContentElement {


    public function generate() {

        if ( TL_MODE == 'BE' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . strtoupper( $GLOBALS['TL_LANG']['CTE']['catalogVisibilityPanelStart'][0] ) . ' ###';

            return $objTemplate->parse();
        }

        return parent::generate();
    }


    protected function compile() {}
}