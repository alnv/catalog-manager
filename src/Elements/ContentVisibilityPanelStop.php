<?php

namespace Alnv\CatalogManagerBundle\Elements;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class ContentVisibilityPanelStop extends ContentElement
{

    protected $strTemplate = 'ce_visibility_panel_stop';


    public function generate()
    {

        if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . strtoupper($GLOBALS['TL_LANG']['CTE']['catalogVisibilityPanelStop'][0]) . ' ###';

            return $objTemplate->parse();
        }

        return parent::generate();
    }


    protected function compile()
    {
    }
}