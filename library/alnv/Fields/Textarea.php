<?php

namespace CatalogManager;

class Textarea {

    
    public static function generate( $arrDCAField, $arrField ) {

        $arrDCAField['eval']['decodeEntities'] = true;
        $arrDCAField['eval']['readonly'] = Toolkit::getBooleanByValue( $arrField['readonly'] );

        if ( $arrField['rte'] ) {

            $arrDCAField['eval']['rte'] = $arrField['rte'];
            $arrDCAField['eval']['allowHtml'] = true;
        }

        if ( $arrField['textareaCols'] ) {

            $arrDCAField['eval']['cols'] = $arrField['textareaCols'];
        }

        if ( $arrField['textareaRows'] ) {

            $arrDCAField['eval']['rows'] = $arrField['textareaRows'];
        }

        if ( $arrField['minlength'] ) {

            $arrDCAField['eval']['minlength'] = intval( $arrField['minlength'] );
        }

        if ( $arrField['maxlength'] ) {

            $arrDCAField['eval']['maxlength'] = intval( $arrField['maxlength'] );
        }

        if ( !$arrDCAField['eval']['tl_class'] ) {

            $arrDCAField['eval']['tl_class'] = 'clr';
        }

        return $arrDCAField;
    }


    public static function parseValue( $varValue, $arrField, $arrCatalog ) {

        if ( !$varValue ) return '';

        return $varValue;
    }
}