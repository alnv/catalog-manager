<?php

namespace CatalogManager;

class ModuleCatalogTaxonomyTree extends \Module {


    protected $strTemplate = 'mod_catalog_taxonomy';


    public function generate() {

        if ( TL_MODE == 'BE' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->title = $this->headline;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            $objTemplate->wildcard = '### ' . utf8_strtoupper( $GLOBALS['TL_LANG']['FMD']['catalogTaxonomyTree'][0] ) . ' ###';

            return $objTemplate->parse();
        }

        return parent::generate();
    }


    protected function compile() {

        $this->Import('CatalogTaxonomy');

        $this->CatalogTaxonomy->arrOptions = $this->arrData;
        $this->CatalogTaxonomy->initialize();
    }
}