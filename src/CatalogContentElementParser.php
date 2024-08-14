<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Frontend;
use Contao\Input;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class CatalogContentElementParser extends Frontend
{

    public function parseVisibilityPanels($objElement, $strBuffer)
    {

        $strIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));

        if (!$strIsBackend && $objElement->type == 'catalogVisibilityPanelStart') {

            if (Input::get('auto_item') && !$objElement->catalogNegateVisibility) {
                $GLOBALS['TL_CATALOG_MANAGER']['VISIBILITY_PANEL'] = TRUE;
            }

            if (!Input::get('auto_item') && $objElement->catalogNegateVisibility) {
                $GLOBALS['TL_CATALOG_MANAGER']['VISIBILITY_PANEL'] = TRUE;
            }
        }

        if ($GLOBALS['TL_CATALOG_MANAGER']['VISIBILITY_PANEL']) $strBuffer = '';

        if (!$strIsBackend && $objElement->type == 'catalogVisibilityPanelStop') {
            $GLOBALS['TL_CATALOG_MANAGER']['VISIBILITY_PANEL'] = FALSE;
        }

        return $strBuffer;
    }
}