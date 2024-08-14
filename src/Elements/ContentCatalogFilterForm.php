<?php

namespace Alnv\CatalogManagerBundle\Elements;

use Alnv\CatalogManagerBundle\CatalogFormFilter;
use Alnv\CatalogManagerBundle\Toolkit;
use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class ContentCatalogFilterForm extends ContentElement
{

    protected $objForm = null;
    protected $strTemplate = 'ce_catalog_filterform';


    public function generate()
    {

        $blnIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));

        if ($blnIsBackend) {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . strtoupper($GLOBALS['TL_LANG']['CTE']['catalogFilterForm'][0]) . ' ###';

            return $objTemplate->parse();
        }

        $this->objForm = new CatalogFormFilter($this->catalogForm);
        $strTemplate = $this->objForm->getCustomTemplate();

        if (!Toolkit::isEmpty($strTemplate)) {
            $this->strTemplate = $strTemplate;
        }

        if (!Toolkit::isEmpty($this->customCatalogElementTpl)) {
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
