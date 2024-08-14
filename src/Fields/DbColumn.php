<?php

namespace Alnv\CatalogManagerBundle\Fields;

use Alnv\CatalogManagerBundle\Toolkit;
use Contao\StringUtil;
use Contao\Validator;

class DbColumn
{

    public static function generate($arrDCAField, $arrField): array
    {

        unset($arrDCAField['inputType']);
        unset($arrDCAField['exclude']);

        $arrDCAField['eval'] = [];
        $arrDCAField['eval']['multiple'] = Toolkit::getBooleanByValue($arrField['multiple']);
        $arrDCAField['eval']['doNotCopy'] = Toolkit::getBooleanByValue($arrField['doNotCopy']);

        if ($arrDCAField['eval']['multiple']) {

            $arrDCAField['eval']['csv'] = ',';
        }

        $arrDCAField['disableFEE'] = true;

        return $arrDCAField;
    }

    public static function parseValue($varValue, $arrField, $arrCatalog = [])
    {

        $varValue = StringUtil::deserialize($varValue);
        if ($arrField['multiple'] && is_string($varValue)) {
            $varValue = explode(',', $varValue);
        }

        if (is_array($varValue) && !empty($varValue)) {
            foreach ($varValue as $strIndex => $strValue) {
                if ($strValue && is_string($strValue) && Validator::isBinaryUuid($strValue)) {
                    $varValue[$strIndex] = StringUtil::binToUuid($strValue);
                }
            }
        }

        return $varValue;
    }
}