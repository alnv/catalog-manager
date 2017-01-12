<?php

namespace CatalogManager;

class Date {

    public static function generate( $arrDCAField, $arrField ) {

        if ( $arrField['rgxp'] ) {

            $arrDCAField['eval']['rgxp'] = $arrField['rgxp'];
        }

        if ( $arrField['readonly'] ) {

            $arrDCAField['eval']['readonly'] = $arrField['readonly'] ? true : false;
        }

        return $arrDCAField;
    }
}