<?php

if ( \Input::get('do') ) {
    
    $arrCatalogs = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'];

    if ( !empty( $arrCatalogs ) && is_array( $arrCatalogs ) ) {

        foreach ( $arrCatalogs as $strTablename => $arrCatalog ) {

            if ( !$arrCatalog['tablename'] ) continue;

            if ( $arrCatalog['tablename'] == \Input::get( 'do' ) ) {

                $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = $arrCatalog['tablename'];
                
                break;
            }

            if ( $arrCatalog['pTable'] == \Input::get( 'do' ) ) {

                $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = $arrCatalog['tablename'];
                
                break;
            }
        }
    }
}