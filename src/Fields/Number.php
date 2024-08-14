<?php

namespace Alnv\CatalogManagerBundle\Fields;

use Alnv\CatalogManagerBundle\Toolkit;

class Number
{


    public static function generate($arrDCAField, $arrField): array
    {

        $arrDCAField['eval']['rgxp'] = static::setRGXP($arrField['rgxp']);
        $arrDCAField['eval']['readonly'] = Toolkit::getBooleanByValue($arrField['readonly']);

        if ($arrField['minval']) {

            $arrDCAField['eval']['minval'] = intval($arrField['minval']);
        }

        if ($arrField['maxval']) {

            $arrDCAField['eval']['maxval'] = intval($arrField['maxval']);
        }

        return $arrDCAField;
    }


    private static function setRGXP($strRGXP)
    {

        if (!$strRGXP) {

            return 'natural';
        }

        return $strRGXP;
    }


    public static function parseValue($varValue, $arrField, $arrCatalog)
    {

        if (is_null($varValue)) return '';

        if (is_numeric($varValue)) return (string)$varValue;

        return $varValue;
    }
}