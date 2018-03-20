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

        if ( Toolkit::isEmpty( $strRgxp ) ) return 'date';

        return $strRgxp;
    }


    public static function parseValue( $varValue, $arrField, $arrCatalog ) {

        if ( Toolkit::isEmpty( $varValue ) ) return '';

        $strRgxp = $arrField['rgxp'] ?: 'datim';
        $strDateFormat = \Date::getFormatFromRgxp( $strRgxp );

        try {

            $objDate = new \Date( $varValue, $strDateFormat );
        }

        catch ( \OutOfBoundsException $objError ) {

            return '';
        }

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

            case 'monthBegin':

                return $objDate->monthBegin;

                break;

            case 'yearBegin':

                return $objDate->yearBegin;

                break;
        }

        return $objDate->timestamp;
    }
}