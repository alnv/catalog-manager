<?php

namespace CatalogManager;

class Select {


    public static $arrCache = [];


    public static function generate( $arrDCAField, $arrField, $objModule = null, $blnActive = true ) {

        $arrDCAField['eval']['chosen'] =  Toolkit::getBooleanByValue($arrField['chosen'] ?? '');
        $arrDCAField['eval']['disabled'] = Toolkit::getBooleanByValue($arrField['disabled'] ?? '');
        $arrDCAField['eval']['multiple'] =  Toolkit::getBooleanByValue($arrField['multiple'] ?? '');
        $arrDCAField['eval']['submitOnChange'] =  Toolkit::getBooleanByValue($arrField['submitOnChange'] ?? '');
        $arrDCAField['eval']['includeBlankOption'] =  Toolkit::getBooleanByValue($arrField['includeBlankOption'] ?? '');

        if ( $arrField['blankOptionLabel'] && is_string( $arrField['blankOptionLabel'] ) ) {

            $arrDCAField['eval']['blankOptionLabel'] = $arrField['blankOptionLabel'];
        }

        $strModuleID = !is_null( $objModule ) && is_object( $objModule ) ? $objModule->id : '';

        if ($blnActive) $arrDCAField = static::getOptions( $arrDCAField, $arrField, $strModuleID, $blnActive );

        if ($arrDCAField['eval']['multiple'] )  $arrDCAField['eval']['csv'] = ',';

        if ($arrField['addRelationWizard'] && in_array( $arrField['optionsType'], [ 'useDbOptions', 'useForeignKey' ] ) && !$arrDCAField['eval']['multiple']) {

            if ( $arrField['dbTable'] && $arrField['dbTableKey'] == 'id' ) {
                
                $arrDCAField['wizard'] = [ [ 'CatalogManager\DcCallbacks', 'generateRelationWizard' ] ];
                $arrDCAField['eval']['chosen'] = true;
                $arrDCAField['eval']['submitOnChange'] = true;
                $arrDCAField['eval']['tl_class'] .= $arrDCAField['eval']['tl_class'] ? ' wizard' : 'wizard';
            }
        }

        return $arrDCAField;
    }


    public static function parseValue( $varValue, $arrField, $arrCatalog ) {

        if (!$varValue) return $arrField['multiple'] ? [] : '';


        static::getOptionsFromCache($arrField['fieldname'], $arrField);

        if ($arrField['multiple']) {

            $arrReturn = [];
            $varValue = Toolkit::parseMultipleOptions($varValue);

            if ( !empty($varValue) && is_array($varValue)) {

                foreach ($varValue as $strValue) {

                    $arrSet = static::$arrCache[$arrField['fieldname']] ?? [];

                    $arrReturn[$strValue] = $arrSet[$strValue] ?? $strValue;
                }
            }

            return $arrReturn;
        }

        $arrSet = static::$arrCache[$arrField['fieldname']] ?? [];

        return $arrSet[$varValue] ?? $varValue;
    }

    
    protected static function getOptionsFromCache( $strFieldname, $arrField ) {

        if ( !isset(static::$arrCache[ $strFieldname ] ) ) static::$arrCache[ $strFieldname ] = [];

        if ( empty( static::$arrCache[ $strFieldname ] ) && is_array( static::$arrCache[ $strFieldname ] ) ) {

            $objOptionGetter = new OptionsGetter( $arrField );
            static::$arrCache[ $strFieldname ] = $objOptionGetter->getOptions();
        }
    }


    protected static function getOptions( $arrDCAField, $arrField, $strID, $blnActive ) {

        $objOptionGetter = new OptionsGetter( $arrField, $strID );

        if ( $objOptionGetter->isForeignKey() ) {

            $arrField['dbTableKey'] = 'id';
            $strForeignKey = $objOptionGetter->getForeignKey();

            if ( $strForeignKey ) {

                $arrDCAField['foreignKey'] = $strForeignKey;
            }
        }

        else {

            $arrDCAField['options'] = $objOptionGetter->getOptions();
            $arrDCAField['reference'] = $arrDCAField['options'];
        }

        return $arrDCAField;
    }
}