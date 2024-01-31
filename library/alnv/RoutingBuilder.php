<?php

namespace CatalogManager;

class RoutingBuilder extends \Frontend
{


    public function initialize($arrFragments)
    {

        if (count($arrFragments) > 1) {

            $arrReturn = [];
            $strCatalogRouting = '';
            $objPage = \PageModel::findPublishedByIdOrAlias($arrFragments[0]);

            if ($objPage !== null) {

                if ($objPage->catalogUseRouting) {

                    $strCatalogRouting = $objPage->catalogRouting ? $objPage->catalogRouting : '';
                }
            }

            if ($strCatalogRouting) {

                $intFragmentIndex = 1;
                $arrFilteredFragments = [];
                $arrReturn[] = $arrFragments[0];
                $arrRoutingFragments = Toolkit::getRoutingParameter($strCatalogRouting);

                if (in_array('auto_item', $arrFragments)) {

                    foreach ($arrFragments as $strFragment) {

                        if ($strFragment === 'auto_item' || !$strFragment) continue;

                        $arrFilteredFragments[] = $strFragment;
                    }
                } else {

                    $arrFilteredFragments = $arrFragments;
                }

                if (!empty($arrRoutingFragments) && is_array($arrRoutingFragments)) {

                    foreach ($arrRoutingFragments as $arrRoutingFragment) {

                        if (!isset($arrFilteredFragments[$intFragmentIndex]) || !$arrFilteredFragments[$intFragmentIndex]) continue;

                        if ($arrRoutingFragment === 'auto_item') {

                            $arrReturn[] = 'auto_item';
                            $arrReturn[] = $arrFilteredFragments[$intFragmentIndex];

                            continue;
                        }

                        \Input::setGet($arrRoutingFragment, $arrFilteredFragments[$intFragmentIndex]);

                        $intFragmentIndex++;
                    }
                }

                return $arrReturn;
            }
        }

        return $arrFragments;
    }
}