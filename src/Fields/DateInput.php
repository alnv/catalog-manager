<?php

namespace Alnv\CatalogManagerBundle\Fields;

use Alnv\CatalogManagerBundle\Toolkit;
use Contao\Date;

class DateInput
{


    public static function generate($arrDCAField, $arrField): array
    {

        $arrDCAField['eval']['datepicker'] = true;
        $arrDCAField['eval']['tl_class'] .= ' wizard';
        $arrDCAField['eval']['rgxp'] = static::setRGXP($arrField['rgxp'] ?? '');
        $arrDCAField['eval']['readonly'] = Toolkit::getBooleanByValue($arrField['readonly'] ?? '');

        if (isset($arrField['readonly']) && $arrField['readonly']) {
            $arrDCAField['eval']['readonly'] = Toolkit::getBooleanByValue($arrField['readonly']);
        }

        if (isset($arrField['tstampAsDefault']) && $arrField['tstampAsDefault']) {
            $arrDCAField['default'] = time();
        }

        return $arrDCAField;
    }


    private static function setRGXP($strRgxp)
    {

        if (Toolkit::isEmpty($strRgxp)) return 'date';

        return $strRgxp;
    }


    public static function parseValue($varValue, $arrField, $arrCatalog)
    {

        if (Toolkit::isEmpty($varValue)) return '';

        $strRgxp = $arrField['rgxp'] ?? 'datim';
        $strDateFormat = Date::getFormatFromRgxp($strRgxp);

        try {

            $objDate = new Date($varValue, $strDateFormat);
        } catch (\OutOfBoundsException $objError) {

            return '';
        }

        switch ($strRgxp) {
            case 'date':
                return $objDate->date;
            case 'time':
                return $objDate->time;
            case 'datim':
                return $objDate->datim;
            case 'monthBegin':
                return $objDate->monthBegin;
            case 'yearBegin':
                return $objDate->yearBegin;
            case 'dayBegin':
                return $objDate->dayBegin;
        }

        return $objDate->timestamp;
    }
}