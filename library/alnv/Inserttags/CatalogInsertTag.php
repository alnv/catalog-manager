<?php

namespace CatalogManager;

class CatalogInsertTag extends \Frontend
{


    public function getInsertTagValue($strTag)
    {

        $arrTags = explode('::', $strTag);

        if (empty($arrTags) || !is_array($arrTags)) return false;

        if (isset($arrTags[0]) && $arrTags[0] == 'CTLG_LIST') {

            $strDefault = '';
            $strModuleId = '';
            $blnActive = true;
            $strDelimiter = ',';
            $strReturnField = 'id';

            if (isset($arrTags[1]) && strpos($arrTags[1], '?') !== false) {

                $arrChunks = explode('?', urldecode($arrTags[1]), 2);
                $strSource = \StringUtil::decodeEntities($arrChunks[1]);
                $strSource = str_replace('[&]', '&', $strSource);
                $arrParams = explode('&', $strSource);

                foreach ($arrParams as $strParam) {

                    list($strKey, $strOption) = explode('=', $strParam);

                    switch ($strKey) {

                        case 'module':

                            $strModuleId = $strOption;

                            break;

                        case 'get':

                            $strReturnField = $strOption;

                            break;

                        case 'default':

                            $strDefault = $strOption;

                            break;

                        case 'delimiter':

                            $strDelimiter = $strOption;

                            break;

                        case 'if':

                            $blnIfActive = false;
                            $arrOptions = explode(',', $strOption);

                            if (is_array($arrOptions) && !empty($strOption)) {

                                foreach ($arrOptions as $strField) {

                                    $strValue = \Input::get($strField);

                                    if (!\Toolkit::isEmpty($strValue)) {

                                        $blnIfActive = true;

                                        break;
                                    }
                                }
                            }

                            $blnActive = $blnIfActive;

                            break;
                    }
                }
            }

            if (!$strModuleId || !$blnActive) return '';

            $objModule = $this->Database->prepare('SELECT * FROM tl_module WHERE id = ? AND `type` = ?')->limit(1)->execute($strModuleId, 'catalogUniversalView');

            if (!$objModule->numRows) return '';

            $this->import('CatalogInput');
            $this->import('SQLQueryBuilder');
            $this->import('CatalogFieldBuilder');

            $arrQuery = [

                'table' => $objModule->catalogTablename,
                'where' => []
            ];

            $arrTaxonomies = Toolkit::deserialize($objModule->catalogTaxonomies);
            $this->CatalogFieldBuilder->initialize($objModule->catalogTablename);
            $arrCatalog = $this->CatalogFieldBuilder->getCatalog();

            if (is_array($arrTaxonomies['query']) && !empty($arrTaxonomies['query']) && $objModule->catalogUseTaxonomies) {

                $arrQuery['where'] = Toolkit::parseQueries($arrTaxonomies['query']);
            }

            if (is_array($arrCatalog['operations']) && in_array('invisible', $arrCatalog['operations']) && !BE_USER_LOGGED_IN) {

                $dteTime = \Date::floorToMinute();

                $arrQuery['where'][] = [

                    'field' => 'tstamp',
                    'operator' => 'gt',
                    'value' => 0
                ];

                $arrQuery['where'][] = [

                    [
                        'value' => '',
                        'field' => 'start',
                        'operator' => 'equal'
                    ],

                    [
                        'field' => 'start',
                        'operator' => 'lte',
                        'value' => $dteTime
                    ]
                ];

                $arrQuery['where'][] = [

                    [
                        'value' => '',
                        'field' => 'stop',
                        'operator' => 'equal'
                    ],

                    [
                        'field' => 'stop',
                        'operator' => 'gt',
                        'value' => $dteTime
                    ]
                ];

                $arrQuery['where'][] = [

                    'field' => 'invisible',
                    'operator' => 'not',
                    'value' => '1'
                ];
            }

            if ($objModule->catalogUseRadiusSearch) {

                $arrRSValues = [];
                $arrRSAttributes = ['rs_cty', 'rs_strt', 'rs_pstl', 'rs_cntry', 'rs_strtn'];

                foreach ($arrRSAttributes as $strSRAttribute) {

                    $strValue = $this->CatalogInput->getActiveValue($strSRAttribute);

                    if (!Toolkit::isEmpty($strValue) && is_string($strValue)) {

                        $arrRSValues[$strSRAttribute] = $strValue;
                    }
                }

                if (!empty($arrRSValues) && is_array($arrRSValues)) {

                    if (!$arrRSValues['rs_cntry'] && $objModule->catalogRadioSearchCountry) $arrRSValues['rs_cntry'] = $objModule->catalogRadioSearchCountry;

                    $objGeoCoding = new GeoCoding();
                    $objGeoCoding->setCity($arrRSValues['rs_cty']);
                    $objGeoCoding->setStreet($arrRSValues['rs_strt']);
                    $objGeoCoding->setPostal($arrRSValues['rs_pstl']);
                    $objGeoCoding->setCountry($arrRSValues['rs_cntry']);
                    $objGeoCoding->setStreetNumber($arrRSValues['rs_strtn']);
                    $strDistance = $this->CatalogInput->getActiveValue('rs_dstnc');
                    $arrCords = $objGeoCoding->getCords('', 'en', true);

                    if (Toolkit::isEmpty($strDistance) || is_array($strDistance)) $strDistance = '50';

                    if ($arrCords['lat'] && $arrCords['lng']) {

                        $arrQuery['distance'] = [

                            'value' => $strDistance,
                            'latCord' => $arrCords['lat'],
                            'lngCord' => $arrCords['lng'],
                            'latField' => $objModule->catalogFieldLat,
                            'lngField' => $objModule->catalogFieldLng
                        ];
                    }
                }
            }

            if ($objModule->catalogEnableParentFilter) {

                if (\Input::get('pid')) {

                    $arrQuery['where'][] = [

                        'field' => 'pid',
                        'operator' => 'equal',
                        'value' => \Input::get('pid')
                    ];
                }
            }

            $arrOrderBy = \StringUtil::deserialize($objModule->catalogOrderBy, true);

            if (is_array($arrOrderBy) && !empty($arrOrderBy)) {

                foreach ($arrOrderBy as $arrOrder) {

                    $arrQuery['orderBy'][] = [

                        'field' => $arrOrder['key'],
                        'order' => $arrOrder['value']
                    ];
                }
            }

            if ($objModule->catalogPerPage || $objModule->catalogOffset) {

                $arrQuery['pagination'] = [

                    'limit' => (int)$objModule->catalogPerPage,
                    'offset' => (int)$objModule->catalogOffset
                ];
            }

            $arrReturn = [];
            $objEntities = $this->SQLQueryBuilder->execute($arrQuery);

            if (!$objEntities->numRows) {

                return $strDefault;
            }

            while ($objEntities->next()) {

                $strValue = $objEntities->{$strReturnField};

                if (Toolkit::isEmpty($strValue)) {

                    continue;
                }

                $arrValues = explode(',', $strValue);

                if (!is_array($arrValues) || empty($arrValues)) {

                    continue;
                }

                foreach ($arrValues as $strValue) {

                    $arrReturn[] = $strValue;
                }
            }

            $arrReturn = array_unique($arrReturn);

            return implode($strDelimiter, $arrReturn);
        }

        if (isset($arrTags[0]) && $arrTags[0] == 'CTLG_FORM') {

            $strFormId = $arrTags[1];

            if (!$strFormId) {

                return '';
            }

            $objForm = new CatalogFormFilter($strFormId);

            if (!$objForm->getState()) {

                return '';
            }

            if ($objForm->disableAutoItem()) {

                return '';
            }

            return $objForm->render();
        }

        if (isset($arrTags[0]) && $arrTags[0] == 'CTLG_ENTITY_URL') {

            $strModuleId = $arrTags[1];
            $strEntityId = $arrTags[2];

            if (!$strModuleId || !$strEntityId) {

                return '';
            }

            return Toolkit::getEntityUrl($strModuleId, $strEntityId);
        }

        return false;
    }
}