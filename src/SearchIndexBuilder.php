<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Frontend;
use Contao\Config;
use Contao\PageModel;
use Contao\Environment;
use Contao\Date;
use Contao\System;

class SearchIndexBuilder extends Frontend
{

    protected array $arrRoutings = [];

    public function initialize($arrPages, $intRoot = 0, $blnIsSitemap = false)
    {

        $arrRoot = [];
        $this->arrRoutings = [];
        $this->import(SQLQueryBuilder::class, 'SQLQueryBuilder');

        if ($intRoot > 0) $arrRoot = $this->Database->getChildRecords($intRoot, 'tl_page');

        $arrProcessed = [];
        $objModules = $this->Database->prepare('SELECT * FROM tl_module WHERE type = ?')->execute('catalogUniversalView');

        while ($objModules->next()) {

            if (!$objModules->catalogUseMasterPage) continue;
            if (!$objModules->catalogMasterPage) continue;
            if (!$objModules->catalogTablename) continue;

            if (!empty($arrRoot) && !in_array($objModules->catalogMasterPage, $arrRoot)) continue;

            if (!isset($arrProcessed[$objModules->catalogMasterPage])) {

                $objParent = $this->getPageModelWithDetailsByID($objModules->catalogMasterPage, $objModules, $blnIsSitemap);

                if ($objParent === null) continue;

                $arrProcessed[$objModules->catalogMasterPage] = $this->setProcessedDomain($objParent, $objModules->catalogTablename);
            }

            $objCatalog = $this->Database->prepare('SELECT * FROM tl_catalog WHERE tablename = ?')->limit(1)->execute($objModules->catalogTablename);

            if (!$objCatalog->numRows) continue;

            $arrCatalog = Toolkit::parseCatalog($objCatalog->row());

            $arrQuery = [
                'where' => [],
                'table' => $objModules->catalogTablename
            ];

            $strUrl = $arrProcessed[$objModules->catalogMasterPage];
            $strQuery = sprintf('SELECT * FROM %s', $objModules->catalogTablename);

            if ($objModules->type == 'catalogUniversalView' && $objModules->catalogTaxonomies) {
                $arrTaxonomies = Toolkit::parseStringToArray($objModules->catalogTaxonomies);

                if (is_array($arrTaxonomies) && isset($arrTaxonomies['query'])) {
                    $arrQuery['where'] = Toolkit::parseQueries($arrTaxonomies['query']);
                }
            }

            if (is_array($arrCatalog['operations']) && in_array('invisible', $arrCatalog['operations'])) {
                $dteTime = Date::floorToMinute();
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

            $strQuery = $strQuery . $this->SQLQueryBuilder->getWhereQuery($arrQuery);
            $arrValues = $this->SQLQueryBuilder->getValues();

            if (isset($GLOBALS['TL_HOOKS']['catalogManagerGetSearchablePagesQuery']) && is_array($GLOBALS['TL_HOOKS']['catalogManagerGetSearchablePagesQuery'])) {
                foreach ($GLOBALS['TL_HOOKS']['catalogManagerGetSearchablePagesQuery'] as $arrCallback) {
                    if (is_array($arrCallback)) {
                        $this->import($arrCallback[0]);
                        $strQuery = $this->{$arrCallback[0]}->{$arrCallback[1]}($strQuery, $objModules->catalogTablename, $arrQuery);
                    }
                }
            }

            $objEntities = $this->Database->prepare($strQuery)->execute($arrValues);

            if (!$objEntities->numRows) continue;

            while ($objEntities->next()) {
                $strSiteMapUrl = $this->createMasterUrl($arrCatalog, $objEntities, $strUrl, $objModules->catalogTablename);
                if (isset($GLOBALS['TL_HOOKS']['catalogManagerAlterSitemapUrl']) && is_array($GLOBALS['TL_HOOKS']['catalogManagerAlterSitemapUrl'])) {
                    foreach ($GLOBALS['TL_HOOKS']['catalogManagerAlterSitemapUrl'] as $arrCallback) {
                        if (is_array($arrCallback)) {
                            $this->import($arrCallback[0]);
                            $strSiteMapUrl = $this->{$arrCallback[0]}->{$arrCallback[1]}($strSiteMapUrl, $objModules->catalogTablename, $strUrl);
                        }
                    }
                }

                if ($strSiteMapUrl && !in_array($strSiteMapUrl, $arrPages)) $arrPages[] = $strSiteMapUrl;
            }
        }

        return $arrPages;
    }

    protected function createMasterUrl($arrCatalog, $objEntities, $strUrl, $strTablename)
    {

        $strBase = '';
        $strUrl = rawurldecode($strUrl);
        $strRootDir = System::getContainer()->getParameter('kernel.project_dir');

        if ($arrCatalog['useRedirect'] && $arrCatalog['internalUrlColumn']) {
            if ($objEntities->{$arrCatalog['internalUrlColumn']}) {
                $intPageID = intval(preg_replace('/[^0-9]+/', '', $objEntities->{$arrCatalog['internalUrlColumn']}));
                $objParent = $this->getPageModelWithDetailsByID($intPageID);
                $strDomain = ($objParent->rootUseSSL ? 'https://' : 'http://') . ($objParent->domain ?: Environment::get('host')) . $strRootDir . '/';

                return $strDomain . $objParent->getFrontendUrl();
            }
        }

        if ($arrCatalog['useRedirect'] && $arrCatalog['externalUrlColumn']) {
            if ($objEntities->{$arrCatalog['externalUrlColumn']}) {
                return null;
            }
        }

        $arrParameters = [];

        if (isset($this->arrRoutings[$strTablename]) && is_array($this->arrRoutings[$strTablename])) {
            foreach ($this->arrRoutings[$strTablename] as $strParameter) {
                $arrParameters[] = $objEntities->{$strParameter} ? $objEntities->{$strParameter} : ' ';
            }
        }

        $arrParameters[] = ($objEntities->alias != '' && !Config::get('disableAlias')) ? $objEntities->alias : $objEntities->id;

        return $strBase . vsprintf($strUrl, $arrParameters);
    }

    protected function getPageModelWithDetailsByID($intPageID, $objCatalog = null, $blnIsSitemap = false)
    {

        $dteTime = Date::floorToMinute();
        $objPage = PageModel::findWithDetails($intPageID);

        if ($objPage === null) return null;

        if (!$objPage->published || ($objPage->start != '' && $objPage->start > $dteTime) || ($objPage->stop != '' && $objPage->stop <= ($dteTime + 60))) return null;

        if ($objCatalog !== null && $objCatalog->catalogNoSearch) return null;
        if ($objCatalog !== null && $blnIsSitemap && $objCatalog->catalogSitemap == 'map_never') return null;

        return $objPage;
    }

    protected function setProcessedDomain($objPage, $strTablename)
    {

        $strRoutings = '';
        $strRootDir = System::getContainer()->getParameter('kernel.project_dir');
        $strDomain = ($objPage->rootUseSSL ? 'https://' : 'http://') . ($objPage->domain ?: Environment::get('host')) . $strRootDir . '/';

        if ($objPage->catalogUseRouting) {
            $this->arrRoutings[$strTablename] = Toolkit::getRoutingParameter($objPage->catalogRouting);
            if (in_array('auto_item', $this->arrRoutings[$strTablename])) unset($this->arrRoutings[$strTablename]['auto_item']);
        }

        if (isset($this->arrRoutings[$strTablename]) && is_array($this->arrRoutings[$strTablename])) {
            $arrRoutings = array_keys($this->arrRoutings[$strTablename]);
            $strRoutings = implode('', array_fill(0, count($arrRoutings), '/%s'));
        }

        return $strDomain . $objPage->getFrontendUrl(((Config::get('useAutoItem') && !Config::get('disableAlias')) ? $strRoutings . '/%s' : $strRoutings . '/items/%s'));
    }
}