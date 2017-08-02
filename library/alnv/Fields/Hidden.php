<?php

namespace CatalogManager;

class Hidden {


    public static function generate( $arrDCAField, $arrField ) {

        $arrDCAField = Text::generate( $arrDCAField, $arrField );

        if ( $arrField['tstampAsDefault'] ) {

            $arrDCAField['default'] = time();
        }
        
        return $arrDCAField;
    }
}