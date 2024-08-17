<?php

namespace Alnv\CatalogManagerBundle\Inserttags;

use Contao\Frontend;
use Alnv\CatalogManagerBundle\CatalogInput;
use Contao\Date;
use Contao\Validator;
use Contao\StringUtil;

class TimestampInsertTag extends Frontend
{

    public function getInsertTagValue($strTag)
    {

        $arrTags = explode('::', $strTag);

        if (empty($arrTags) || !is_array($arrTags)) {
            return false;
        }

        $strInsertTagName = strtoupper($arrTags[0] ?? '');

        if ($strInsertTagName == 'CTLG_TIMESTAMP') {

            $objToday = new Date();
            $objDate = new Date($objToday->date);
            $strMethod = isset($arrTags[2]) ? $arrTags[2] : 'dayBegin';
            $intTstamp = $objDate->{$strMethod};

            if (isset($arrTags[1]) && strpos($arrTags[1], '?') !== false) {

                $arrChunks = explode('?', urldecode($arrTags[1]), 2);
                $strSource = StringUtil::decodeEntities($arrChunks[1]);
                $strSource = str_replace('[&]', '&', $strSource);
                $arrParams = explode('&', $strSource);

                foreach ($arrParams as $strParam) {

                    list($strKey, $strOption) = explode('=', $strParam);

                    switch ($strKey) {
                        case 'active':
                            $objInput = new CatalogInput();
                            $strValue = $objInput->getValue($strOption);
                            if (Validator::isDate($strValue)) {
                                $objDate = new Date($strValue);
                                $strValue = $objDate->{$strMethod};
                            }
                            $intTstamp = (int)$strValue;
                            if (!$intTstamp) {
                                return '';
                            }
                            if (isset($arrTags[2])) {
                                $objWatchedDate = new Date($intTstamp);
                                $intTstamp = $objWatchedDate->{$strMethod};
                            }
                            break;
                        case 'watch':
                            $objInput = new CatalogInput();
                            $intWatchValue = $objInput->getValue($strOption);
                            if (Validator::isDate($intWatchValue)) {
                                $objDate = new Date($intWatchValue);
                                $intWatchValue = $objDate->{$strMethod};
                            }
                            if ($intWatchValue !== null && $intWatchValue !== '') {
                                $intTstamp = (int)$intWatchValue;
                            }
                            if (isset($arrTags[2])) {
                                $objWatchedDate = new Date($intTstamp);
                                $intTstamp = $objWatchedDate->{$strMethod};
                            }
                            break;
                        case 'add':
                            $strOption = $strOption ? (int)$strOption : 0;
                            $intTstamp = $intTstamp + $strOption;
                            break;
                        case 'subtract':
                            $strOption = $strOption ? (int)$strOption : 0;
                            $intTstamp = $intTstamp - $strOption;
                            break;
                        case 'multiply':
                            $strOption = $strOption ? (int)$strOption : 0;
                            $intTstamp = $intTstamp * $strOption;
                            break;
                        case 'divide':
                            $strOption = $strOption ? (int)$strOption : 0;
                            if ($strOption > 0) {
                                $intTstamp = $intTstamp / $strOption;
                            }
                            break;
                    }
                }
            }

            return $intTstamp;
        }

        return false;
    }
}