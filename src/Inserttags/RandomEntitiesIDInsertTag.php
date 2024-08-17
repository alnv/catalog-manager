<?php

namespace Alnv\CatalogManagerBundle\Inserttags;

use Contao\Config;
use Alnv\CatalogManagerBundle\Toolkit;
use Contao\Frontend;
use Contao\Input;
use Contao\StringUtil;

class RandomEntitiesIDInsertTag extends Frontend
{


    public function getInsertTagValue($strTag)
    {

        $arrTags = explode('::', $strTag);

        if (empty($arrTags) || !is_array($arrTags)) {

            return false;
        }

        $strInsertTagName = strtoupper($arrTags[0] ?? '');

        if ($strInsertTagName == 'CTLG_RANDOM_ENTITY_IDS') {

            $strWhere = '';
            $strLimit = '';
            $arrValues = [];
            $_arrValues = [];
            $strQueryName = '';
            $strTablename = isset($arrTags[1]) ? $arrTags[1] : '';

            if (!$this->Database->tableExists($strTablename)) {
                return false;
            }

            if (isset($arrTags[2]) && strpos($arrTags[2], '?') !== false) {

                $arrChunks = explode('?', urldecode($arrTags[2]), 2);
                $strSource = StringUtil::decodeEntities($arrChunks[1]);
                $strSource = str_replace('[&]', '&', $strSource);
                $arrParams = explode('&', $strSource);

                foreach ($arrParams as $strParam) {

                    list($strKey, $strOption) = explode('=', $strParam);

                    switch ($strKey) {
                        case 'values':
                            $_arrValues = explode(',', $strOption);
                            break;

                        case 'query':
                            $strQueryName = $strOption;
                            break;

                        case 'limit':
                            if (is_numeric($strOption)) {
                                $strLimit = 'LIMIT ' . $strOption;
                            }
                            break;
                    }
                }
            }

            if ($this->Database->fieldExists('invisible', $strTablename)) {
                $strWhere = 'WHERE invisible != ?';
                $arrValues[] = '1';
            }

            if (Config::get('CTLG_RANDOM_QUERY') && $strQueryName) {

                $strQuery = Config::get('CTLG_RANDOM_QUERY')[$strQueryName] ?: '';
                $strQuery = Input::cleanKey($strQuery);

                if ($strQuery) {
                    $strWhere .= $strWhere ? ' AND ' . $strQuery : 'WHERE ' . $strQuery;
                    foreach ($_arrValues as $strValue) {
                        $arrValues[] = Toolkit::replaceInsertTags($strValue);
                    }
                }
            }

            $strIds = [];
            $objIds = $this->Database->prepare(sprintf('SELECT id FROM %s %s ORDER BY RAND() %s', $strTablename, $strWhere, $strLimit))->execute(...$arrValues);

            if ($objIds->numRows) {
                while ($objIds->next()) {
                    $strIds[] = $objIds->id;
                }
            }

            return implode(',', $strIds);
        }

        return false;
    }
}