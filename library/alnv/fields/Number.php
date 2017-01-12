<?php

namespace CatalogManager;

class Number {

    public static function generate( $arrDCAField, $arrField ) {

        if ( $arrField['max'] ) {

            $arrDCAField['eval']['maxval'] = intval(  $arrField['max'] );
        }

        if ( $arrField['min'] ) {

            $arrDCAField['eval']['minval'] = intval(  $arrField['min'] );
        }

        if ( $arrField['readonly'] ) {

            $arrDCAField['eval']['readonly'] = $arrField['readonly'] ? true : false;
        }

        if ( $arrField['rgxp'] ) {

            $arrDCAField['eval']['rgxp'] = $arrField['rgxp'];
        }

        return $arrDCAField;
    }
}