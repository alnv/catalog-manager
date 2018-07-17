<?php

namespace CatalogManager;

class CatalogContentElementParser extends \Frontend {


    public function parseVisibilityPanels( $objElement, $strBuffer ) {

        if ( TL_MODE == 'FE' && $objElement->type == 'catalogVisibilityPanelStart' ) {

            if ( \Input::get( 'auto_item' ) && !$objElement->catalogNegateVisibility ) {

                $GLOBALS['TL_CATALOG_MANAGER']['VISIBILITY_PANEL'] = TRUE;
            }

            if ( !\Input::get( 'auto_item' ) && $objElement->catalogNegateVisibility ) {

                $GLOBALS['TL_CATALOG_MANAGER']['VISIBILITY_PANEL'] = TRUE;
            }
        }

        if ( $GLOBALS['TL_CATALOG_MANAGER']['VISIBILITY_PANEL'] ) $strBuffer = '';

        if ( TL_MODE == 'FE' && $objElement->type == 'catalogVisibilityPanelStop' ) {

            $GLOBALS['TL_CATALOG_MANAGER']['VISIBILITY_PANEL'] = FALSE;
        }

        return $strBuffer;
    }
}