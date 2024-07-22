<?php

namespace CatalogManager;


class ContentCatalogFilterForm extends \ContentElement
{


    protected $objForm = null;
    protected $strTemplate = 'ce_catalog_filterform';


    public function generate()
    {

        if (TL_MODE == 'BE') {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . strtoupper($GLOBALS['TL_LANG']['CTE']['catalogFilterForm'][0]) . ' ###';

            return $objTemplate->parse();
        }

        $this->objForm = new CatalogFormFilter($this->catalogForm);
        $strTemplate = $this->objForm->getCustomTemplate();

        if (TL_MODE == 'FE' && !Toolkit::isEmpty($strTemplate)) {

            $this->strTemplate = $strTemplate;
        }

        if (TL_MODE == 'FE' && !Toolkit::isEmpty($this->customCatalogElementTpl)) {

            $this->strTemplate = $this->customCatalogElementTpl;
        }

        if (!$this->objForm->getState()) {

            return '';
        }

        if ($this->objForm->disableAutoItem()) {

            return '';
        }

        return parent::generate();
    }


    protected function compile()
    {

        $this->objForm->render($this->Template);
    }
}
