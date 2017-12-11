<?php

namespace CatalogManager;

class Text {

    
    public static function generate( $arrDCAField, $arrField, $objModule = null ) {

        $arrDCAField['eval']['readonly'] = Toolkit::getBooleanByValue( $arrField['readonly'] );

        if ( $arrField['rgxp'] ) {

            $arrDCAField['eval']['rgxp'] = $arrField['rgxp'];
        }

        if ( $arrField['minlength'] ) {

            $arrDCAField['eval']['minlength'] = intval( $arrField['minlength'] );
        }

        if ( $arrField['maxlength'] ) {

            $arrDCAField['eval']['maxlength'] = intval( $arrField['maxlength'] );
        }

        if ( $arrField['pagePicker'] ) {

            $arrDCAField['eval']['rgxp'] = 'url';
            $arrDCAField['eval']['decodeEntities'] = true;
            $arrDCAField['eval']['tl_class'] .= ' wizard';
            $arrDCAField['wizard'][] = [ 'CatalogManager\DcCallbacks', 'pagePicker' ];
        }

        if ( $arrField['autoCompletionType'] ) {

            $strModuleID = !is_null( $objModule ) && is_object( $objModule ) ? $objModule->id : '';

            $arrDCAField['inputType'] = 'catalogTextFieldWidget';
            $arrDCAField['eval']['multiple'] = Toolkit::getBooleanByValue( $arrField['multiple'] );

            $objAutoCompletion = new CatalogAutoCompletion( $arrField, $strModuleID );
            $arrDCAField['options'] = $objAutoCompletion->getOptions();
        }

        if ( !Toolkit::isEmpty( $arrField['dynValue'] ) ) {

            if ( !is_array( $arrField['save_callback'] ) ) $arrField['save_callback'] = [];
            
            $arrDCAField['save_callback'][] = [ 'CatalogManager\DcCallbacks', 'setDynValue' ];
        }

        return $arrDCAField;
    }


    public static function parseValue( $varValue, $arrField, $arrCatalog ) {

        $varValue = deserialize( $varValue );
        
        if ( Toolkit::isEmpty( $varValue ) && is_string( $varValue ) ) return '';
        if ( is_array( $varValue ) && empty( $varValue ) ) return [];

        if ( is_array( $varValue ) || $arrField['multiple'] ) {

            $arrReturn = [];
            $varValue = Toolkit::parseMultipleOptions( $varValue );

            if ( !empty( $varValue ) && is_array( $varValue ) ) {

                foreach ( $varValue as $strValue ) {

                    $arrReturn[ $strValue ] = $strValue;
                }
            }

            return $arrReturn;
        }

        return $varValue;
    }
}