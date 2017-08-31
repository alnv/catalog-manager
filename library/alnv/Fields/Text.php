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
            $objAutoCompletion = new CatalogAutoCompletion( $arrField, $strModuleID );

            $arrDCAField['inputType'] = 'catalogTextFieldWidget';
            $arrDCAField['options'] = $objAutoCompletion->getOptions();
            $arrDCAField['eval']['multipleValues'] = $arrField['multiple'] ? true : false;
        }

        return $arrDCAField;
    }
}