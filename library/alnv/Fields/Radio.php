<?php

namespace CatalogManager;

class Radio {


    public static $arrCache = [];


    public static function generate( $arrDCAField, $arrField, $arrCatalog = [], $objModule = null ) {

        $arrDCAField['eval']['disabled'] = Toolkit::getBooleanByValue( $arrField['disabled'] );
        $arrDCAField['eval']['submitOnChange'] =  Toolkit::getBooleanByValue( $arrField['submitOnChange'] );
        $arrDCAField['eval']['includeBlankOption'] =  Toolkit::getBooleanByValue( $arrField['includeBlankOption'] );

        if ( $arrField['blankOptionLabel'] && is_string( $arrField['blankOptionLabel'] ) ) {

            $arrDCAField['eval']['blankOptionLabel'] = $arrField['blankOptionLabel'];
        }

        $strModuleID = !is_null( $objModule ) && is_object( $objModule ) ? $objModule->id : '';
        $objOptionGetter = new OptionsGetter( $arrField, $strModuleID );

        if ( $objOptionGetter->isForeignKey() ) {

            $strForeignKey = $objOptionGetter->getForeignKey();

            if ( $strForeignKey ) {

                $arrDCAField['foreignKey'] = $strForeignKey;
            }
        }

        else {

            $arrDCAField['options'] = $objOptionGetter->getOptions();
        }

        return $arrDCAField;
    }


    public static function parseValue( $varValue, $arrField, $arrCatalog ) {

        if ( !$varValue ) return '';

        static::getOptionsFromCache( $arrField['fieldname'], $arrField );

        if ( !empty( static::$arrCache[ $arrField['fieldname'] ] ) && is_array( static::$arrCache[ $arrField['fieldname'] ] ) ) {

            return static::$arrCache[ $arrField['fieldname'] ][ $varValue ] ? static::$arrCache[ $arrField['fieldname'] ][ $varValue ] : $varValue;
        }

        return $varValue;
    }


    protected static function getOptionsFromCache( $strFieldname, $arrField ) {

        if ( !static::$arrCache[ $strFieldname ]  ) {

            static::$arrCache[ $strFieldname ] = [];
        }

        if ( empty( static::$arrCache[ $strFieldname ] ) && is_array( static::$arrCache[ $strFieldname ] ) ) {

            $objOptionGetter = new OptionsGetter( $arrField );
            static::$arrCache[ $strFieldname ] = $objOptionGetter->getOptions();
        }
    }
}