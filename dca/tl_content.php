<?php

if ( \Input::get('do') ) {
    
    $arrCatalogExtensions = array_keys( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] );

    if ( in_array( \Input::get('do'), $arrCatalogExtensions ) ) {

        $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = \Input::get('do');
    }
}