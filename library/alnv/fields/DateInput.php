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
}