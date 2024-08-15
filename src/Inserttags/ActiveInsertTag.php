<?php

namespace Alnv\CatalogManagerBundle\Inserttags;

use Alnv\CatalogManagerBundle\CatalogInput;
use Alnv\CatalogManagerBundle\Toolkit;
use Contao\Date;
use Contao\Frontend;
use Contao\Validator;
use Contao\StringUtil;

class ActiveInsertTag extends Frontend
{

    public function __construct()
    {

        $this->import(CatalogInput::class, 'CatalogInput');

        parent::__construct();
    }

    public function getInsertTagValue($strTag)
    {

        $arrTags = explode('::', $strTag);
        $strInsertTagName = strtoupper($arrTags[0] ?? '');

        if (is_array($arrTags) && $strInsertTagName == 'CTLG_ACTIVE' && isset($arrTags[1])) {

            global $objPage;

            $varValue = $this->CatalogInput->getActiveValue($arrTags[1]);

            if (isset($arrTags[2]) && strpos($arrTags[2], '?') !== false) {

                $arrChunks = \explode('?', urldecode($arrTags[2]), 2);
                $strSource = StringUtil::decodeEntities($arrChunks[1]);
                $strSource = \str_replace('[&]', '&', $strSource);
                $arrParams = \explode('&', $strSource);

                $blnIsDate = false;
                $strDateMethod = 'tstamp';
                $strDateFormat = $objPage->dateFormat;

                foreach ($arrParams as $strParam) {
                    list($strKey, $strOption) = explode('=', $strParam);
                    switch ($strKey) {
                        case 'default':
                            if (Toolkit::isEmpty($varValue)) $varValue = Toolkit::replaceInsertTags($strOption);
                            break;
                        case 'suffix':
                        case 'prefix':
                            if (is_string($varValue)) {
                                $strFix = Toolkit::replaceInsertTags($strOption);
                                $blnNoFix = false;
                                if ($strKey == 'suffix') {
                                    $blnNoFix = strpos($strFix, substr($varValue, -strlen($strFix))) === false;
                                }
                                if ($strKey == 'prefix') {
                                    $blnNoFix = strpos($strFix, substr($varValue, 0, strlen($strFix))) === false;
                                }
                                if ($blnNoFix) {
                                    $varValue = ($strKey == 'suffix' ? $varValue . $strFix : $strFix . $varValue);
                                }
                            }
                            break;
                        case 'isDate':
                            $blnIsDate = true;
                            break;
                        case 'dateMethod':
                            $strDateMethod = $strOption;
                            break;
                        case 'dateFormat':
                            $strDateFormat = $strOption;
                            break;
                    }
                }

                if ($blnIsDate && is_array($varValue)) {

                    foreach ($varValue as $strK => $strV) {

                        if (!$strV) {
                            unset($varValue[$strK]);
                            continue;
                        };

                        if (Validator::isDate($strV) || Validator::isDate($strV) || Validator::isTime($strV)) {
                            $objDate = new Date($strV, $strDateFormat);
                            $intTimestamp = $objDate->{$strDateMethod};
                            if ($intTimestamp > 0) $varValue[$strK] = $objDate->{$strDateMethod};
                        } else if (is_numeric($strV)) {

                            $objDate = new Date($strV);
                            $intTimestamp = $objDate->{$strDateMethod};

                            if ($intTimestamp > 0) $varValue[$strK] = $objDate->{$strDateMethod};
                        }
                    }
                }

                if ($blnIsDate && is_string($varValue) && !Toolkit::isEmpty($varValue)) {
                    if (Validator::isDate($varValue) || Validator::isDate($varValue) || Validator::isTime($varValue)) {
                        $objDate = new Date($varValue, $strDateFormat);
                        $intTimestamp = $objDate->{$strDateMethod};
                        if ($intTimestamp > 0) $varValue = $objDate->{$strDateMethod};
                    } else if (is_numeric($varValue)) {
                        $objDate = new Date($varValue);
                        $intTimestamp = $objDate->{$strDateMethod};
                        if ($intTimestamp > 0) $varValue = $objDate->{$strDateMethod};
                    }
                }
            } elseif (Toolkit::isEmpty($varValue)) {
                $varValue = $arrTags[2] ?? '';
            }

            if (is_array($varValue)) $varValue = implode(',', $varValue);

            return $varValue;
        }

        return false;
    }
}