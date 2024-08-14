<?php

namespace Alnv\CatalogManagerBundle\Elements;

use Contao\ContentElement;
use Contao\BackendTemplate;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class ContentVisibilityPanelStart extends ContentElement
{


    protected $strTemplate = 'ce_visibility_panel_start';


    public function generate()
    {

        if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . strtoupper($GLOBALS['TL_LANG']['CTE']['catalogVisibilityPanelStart'][0]) . ' ###';

            return $objTemplate->parse();
        }

        return parent::generate();
    }


    protected function compile()
    {
    }
}