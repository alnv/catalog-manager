<?php

namespace CatalogManager;

class Select {

    public static function generate( $arrDCAField, $arrField ) {

        $arrDCAField['eval']['multiple'] =  $arrField['multiple'] ? true : false;

        if ( $arrField['disabled'] ) {

            $arrDCAField['eval']['disabled'] = $arrField['disabled'] ? true : false;
        }

        return $arrDCAField;
    }
}