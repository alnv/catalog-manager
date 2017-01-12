<?php

namespace CatalogManager;

class Checkbox {

    public static function generate( $arrDCAField, $arrField ) {

        if ( $arrField['disabled'] ) {

            $arrDCAField['eval']['disabled'] = $arrField['disabled'] ? true : false;
        }

        return $arrDCAField;
    }
}