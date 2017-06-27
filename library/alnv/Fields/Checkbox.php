<?php

namespace CatalogManager;

class Checkbox {


    public static $arrCache = [];


    public static function generate( $arrDCAField, $arrField, $arrCatalog = [], $objModule = null ) {

        $arrDCAField['eval']['disabled'] = Toolkit::getBooleanByValue( $arrField['disabled'] );
        $arrDCAField['eval']['multiple'] =  Toolkit::getBooleanByValue( $arrField['multiple'] );
        $arrDCAField['eval']['submitOnChange'] =  Toolkit::getBooleanByValue( $arrField['submitOnChange'] );

        $strModuleID = !is_null( $objModule ) && is_object( $objModule ) ? $objModule->id : '';
        $objOptionGetter = new OptionsGetter( $arrField, $strModuleID );

        if ( $objOptionGetter->isForeignKey() ) {

            $strForeignKey = $objOptionGetter->getForeignKey();

            if ( $strForeignKey ) {

                $arrDCAField['foreignKey'] = $strForeignKey;
            }
        }

        else {

            $arrOptions = $objOptionGetter->getOptions();

            if ( !empty( $arrOptions ) ) {

                $arrDCAField['options'] = $arrOptions;
                $arrDCAField['reference'] = $arrDCAField['options'];
            }
        }

        $arrDCAField['eval']['csv'] = ',';

        return $arrDCAField;
    }


    public static function parseValue( $varValue, $arrField, $arrCatalog ) {

        if ( !$varValue ) return [];

        $varValue = Toolkit::parseMultipleOptions( $varValue );

        if ( !empty( $varValue ) && is_array( $varValue ) ) {

            $arrReturn = [];

            static::getOptionsFromCache( $arrField['fieldname'], $arrField );

            if ( !empty( $varValue ) && is_array( $varValue ) ) {

                foreach ( $varValue as $strValue ) {

                    $arrReturn[ $strValue ] = static::$arrCache[ $arrField['fieldname'] ][ $strValue ] ? static::$arrCache[ $arrField['fieldname'] ][ $strValue ] : $strValue;
                }
            }

            return $arrReturn;
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