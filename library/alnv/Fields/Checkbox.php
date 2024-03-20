<?php

namespace CatalogManager;

class Checkbox
{


    public static $arrCache = [];


    public static function generate($arrDCAField, $arrField, $objModule = null, $blnActive = true)
    {

        $arrDCAField['eval']['csv'] = ',';
        $arrDCAField['eval']['disabled'] = Toolkit::getBooleanByValue($arrField['disabled'] ?? '');
        $arrDCAField['eval']['multiple'] = Toolkit::getBooleanByValue($arrField['multiple'] ?? '');
        $arrDCAField['eval']['submitOnChange'] = Toolkit::getBooleanByValue($arrField['submitOnChange'] ?? '');
        $strModuleID = !is_null($objModule) && is_object($objModule) ? $objModule->id : '';

        if ($blnActive) $arrDCAField = static::getOptions($arrDCAField, $arrField, $strModuleID, $blnActive);

        return $arrDCAField;
    }


    public static function parseValue($varValue, $arrField, $arrCatalog)
    {

        if (!$varValue) return $arrField['multiple'] ? [] : '';

        $varValue = Toolkit::parseMultipleOptions($varValue);

        if (!empty($varValue) && is_array($varValue)) {
            $arrReturn = [];
            static::getOptionsFromCache($arrField['fieldname'], $arrField);
            if (!empty($varValue) && is_array($varValue)) {
                foreach ($varValue as $strValue) {
                    $arrReturn[$strValue] = isset(static::$arrCache[$arrField['fieldname']][$strValue]) && static::$arrCache[$arrField['fieldname']][$strValue] ? static::$arrCache[$arrField['fieldname']][$strValue] : $strValue;
                }
            }
            return $arrReturn;
        }

        return $varValue;
    }


    protected static function getOptionsFromCache($strFieldname, $arrField)
    {

        if (!isset(static::$arrCache[$strFieldname]) || !static::$arrCache[$strFieldname]) {
            static::$arrCache[$strFieldname] = [];
        }

        if (empty(static::$arrCache[$strFieldname]) || !is_array(static::$arrCache[$strFieldname])) {
            $objOptionGetter = new OptionsGetter($arrField);
            static::$arrCache[$strFieldname] = $objOptionGetter->getOptions();
        }
    }


    protected static function getOptions($arrDCAField, $arrField, $strID, $blnActive)
    {

        $objOptionGetter = new OptionsGetter($arrField, $strID);

        if ($objOptionGetter->isForeignKey()) {
            $arrField['dbTableKey'] = 'id';
            $strForeignKey = $objOptionGetter->getForeignKey();
            if ($strForeignKey) {
                $arrDCAField['foreignKey'] = $strForeignKey;
            }
        } else {
            $arrOptions = $objOptionGetter->getOptions();
            if (isset($arrField['optionsType']) && $arrField['optionsType'] && is_array($arrOptions)) {
                $arrDCAField['options'] = $arrOptions;
                if (!Toolkit::isNumericArray($arrDCAField['options'])) {
                    $arrDCAField['reference'] = $arrDCAField['options'];
                }
            }
        }

        return $arrDCAField;
    }
}