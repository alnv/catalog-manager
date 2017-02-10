<?php

namespace CatalogManager;

class Number {


    public static function generate( $arrDCAField, $arrField ) {

        $arrDCAField['eval']['rgxp'] = static::setRGXP( $arrField['rgxp'] );
        $arrDCAField['eval']['readonly'] = Toolkit::getBooleanByValue( $arrField['readonly'] );

        if ( $arrField['minval'] ) {

            $arrDCAField['eval']['minval'] = intval(  $arrField['minval'] );
        }

        if ( $arrField['maxval'] ) {

            $arrDCAField['eval']['maxval'] = intval(  $arrField['maxval'] );
        }

        return $arrDCAField;
    }

    
    private static function setRGXP( $strRGXP ) {

        if ( !$strRGXP ) {

            return 'natural';
        }

        return $strRGXP;
    }
}