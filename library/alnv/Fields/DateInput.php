<?php

namespace CatalogManager;

class DateInput {


    public static function generate( $arrDCAField, $arrField ) {

        $arrDCAField['eval']['datepicker'] = true;
        $arrDCAField['eval']['tl_class'] .= ' wizard';
        $arrDCAField['eval']['rgxp'] = static::setRGXP( $arrField['rgxp'] );
        $arrDCAField['eval']['readonly'] = Toolkit::getBooleanByValue( $arrField['readonly'] );

        if ( $arrField['readonly'] ) {

            $arrDCAField['eval']['readonly'] = Toolkit::getBooleanByValue( $arrField['readonly'] );
        }

        if ( $arrField['tstampAsDefault'] ) {

            $arrDCAField['default'] = time();
        }

        return $arrDCAField;
    }

    
    private static function setRGXP( $strRgxp ) {

        if ( !$strRgxp ) {

            return 'date';
        }

        return $strRgxp;
    }


    public static function parseValue( $varValue, $arrField, $arrCatalog ) {

        if ( !$varValue ) return '';

        $strRgxp = $arrField['rgxp'] ? $arrField['rgxp'] : 'datim';
        $strDateFormat = \Date::getFormatFromRgxp( $strRgxp );
        $objDate = new \Date( $varValue, $strDateFormat );

        switch ( $strRgxp ) {

            case 'date':

                return $objDate->date;

                break;

            case 'time':

                return $objDate->time;

                break;

            case 'datim':

                return $objDate->datim;

                break;
        }

        return $objDate->timestamp;
    }
}