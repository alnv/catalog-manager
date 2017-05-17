<?php

namespace CatalogManager;

class DbColumn {


    public static function generate( $arrDCAField, $arrField ) {

        unset( $arrDCAField['inputType'] );
        unset( $arrDCAField['exclude'] );

        $arrDCAField['eval'] = [];
        $arrDCAField['eval']['multiple'] =  Toolkit::getBooleanByValue( $arrField['multiple'] );
        $arrDCAField['eval']['doNotCopy'] = Toolkit::getBooleanByValue( $arrField['doNotCopy'] );

        if ( $arrDCAField['eval']['multiple'] ) {

            $arrDCAField['eval']['csv'] = ',';
        }

        $arrDCAField['disableFEE'] = true;

        return $arrDCAField;
    }


    public static function parseValue( $varValue, $arrField, $arrCatalog = [] ) {

        $varValue = deserialize( $varValue );

        if ( $arrField['multiple'] && is_string( $varValue ) ) {

            $varValue = explode( ',', $varValue );
        }

        return $varValue;
    }
}