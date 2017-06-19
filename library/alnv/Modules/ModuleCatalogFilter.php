<?php

namespace CatalogManager;

class ModuleCatalogFilter extends \Module {


    protected $strTemplate = 'mod_catalog_filter';


    public function generate() {

        if ( TL_MODE == 'BE' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->title = $this->headline;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            $objTemplate->wildcard = '### ' . utf8_strtoupper( $GLOBALS['TL_LANG']['FMD']['catalogFilter'][0] ) . ' ###';

            return $objTemplate->parse();
        }

        if ( TL_MODE == 'FE' && $this->catalogCustomTemplate ) {

            $this->strTemplate = $this->catalogCustomTemplate;
        }

        if ( $this->catalogIgnoreFilterOnAutoItem && \Input::get( 'auto_item' ) ) {

            return null;
        }

        return parent::generate();
    }


    protected function compile() {

        $this->Import('CatalogFilter');

        $this->CatalogFilter->arrOptions = $this->arrData;
        $this->CatalogFilter->strTable = $this->catalogTablename;
        $this->CatalogFilter->initialize();

        $this->Template->formSubmit = 'tl_filters';
        $this->Template->method = $this->catalogFormMethod;
        $this->Template->reset = $this->CatalogFilter->setResetLink();
        $this->Template->output = $this->CatalogFilter->generateForm();
        $this->Template->action = $this->CatalogFilter->setActionAttribute();
        $this->Template->disableSubmit = $this->catalogDisableSubmit ? true : false;
        $this->Template->submit = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['filter'];
    }
}