<?php

namespace Alnv\CatalogManagerBundle\Elements;

class ContentVisibilityPanelStop extends \ContentElement {


    protected $strTemplate = 'ce_visibility_panel_stop';


    public function generate() {

        if ( TL_MODE == 'BE' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . strtoupper( $GLOBALS['TL_LANG']['CTE']['catalogVisibilityPanelStop'][0] ) . ' ###';

            return $objTemplate->parse();
        }

        return parent::generate();
    }


    protected function compile() {}
}