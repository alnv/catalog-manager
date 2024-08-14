<?php

namespace Alnv\CatalogManagerBundle\Modules;

use Alnv\CatalogManagerBundle\Toolkit;
use Contao\Module;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
use Contao\Controller;
use Contao\Date;
use Symfony\Component\HttpFoundation\Request;
use Contao\BackendTemplate;

class ModuleCatalogBookNavigation extends Module
{
    
    protected array $arrFields = [];
    
    protected $strAlias = null;
    
    protected array $arrCatalog = [];
    
    protected $objMasterPage = null;
    
    protected array $arrRoutingParameter = [];
    
    protected $strTemplate = 'mod_catalog_book_navigation';


    public function generate()
    {

        if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {

            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->title = $this->headline;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            $objTemplate->wildcard = '### ' . strtoupper($GLOBALS['TL_LANG']['FMD']['catalogBookNavigation'][0]) . ' ###';

            return $objTemplate->parse();

        }

        $this->strAlias = Input::get('auto_item');

        if (!$this->strAlias) {
            return null;
        }

        return parent::generate();
    }


    protected function compile()
    {

        global $objPage;

        $this->import('SQLQueryBuilder');

        if ($this->catalogMasterPage) {
            $this->objMasterPage = PageModel::findByPk($this->catalogMasterPage);
        }

        if ($objPage->catalogRoutingTable && $objPage->catalogRoutingTable !== $this->catalogTablename) {
            $objPage->catalogUseRouting = '';
        }

        if ($objPage->catalogUseRouting && $objPage->catalogRouting) {
            $this->arrRoutingParameter = Toolkit::getRoutingParameter($objPage->catalogRouting);
        }

        if (empty($this->arrRoutingParameter) && $this->objMasterPage) {
            if ($this->objMasterPage->catalogUseRouting) {
                $this->arrRoutingParameter = Toolkit::getRoutingParameter($this->objMasterPage->catalogRouting);
            }
        }

        $this->catalogTaxonomies = Toolkit::deserialize($this->catalogTaxonomies);
        $this->catalogOrderBy = Toolkit::deserialize($this->catalogOrderBy);

        $arrQuery = [];
        $arrTaxonomies = [];
        $arrNavigationItems = [];
        $arrQuery['table'] = $this->catalogTablename;

        if (!empty($this->catalogTaxonomies['query']) && is_array($this->catalogTaxonomies['query']) && $this->catalogUseTaxonomies) {

            $arrTaxonomies = Toolkit::parseQueries($this->catalogTaxonomies['query']);
        }

        $arrQuery['where'] = $arrTaxonomies;
        $blnVisibility = $this->hasVisibility();

        if ($blnVisibility) {
            $this->addVisibilityQuery($arrQuery);
        }

        if (is_array($this->catalogOrderBy)) {
            if (!empty($this->catalogOrderBy)) {
                foreach ($this->catalogOrderBy as $arrOrderBy) {
                    if ($arrOrderBy['key'] && $arrOrderBy['value']) {
                        $arrQuery['orderBy'][] = [
                            'field' => $arrOrderBy['key'],
                            'order' => $arrOrderBy['value']
                        ];
                    }
                }
            }
        }

        switch ($this->catalogBookNavigationSortingType) {

            case 'manuel':
                $this->catalogBookNavigationItem = 'sorting';
                $arrNavigationItems = $this->getManuelNavigation($arrQuery, $blnVisibility, $arrTaxonomies);
                break;

            case 'custom':
                $arrNavigationItems = $this->getCustomNavigation($arrQuery);
                break;
        }

        foreach ($arrNavigationItems as $strType => $arrNavigation) {

            if (empty($arrNavigation)) {
                unset($arrNavigationItems[$strType]);
                continue;
            }

            $arrNavigation['origin'] = $arrNavigation;
            $arrNavigation['masterUrl'] = $this->getMasterRedirect($arrNavigation, $arrNavigation['alias']);

            foreach ($arrNavigation as $strFieldname => $strValue) {

                $arrNavigation[$strFieldname] = Toolkit::parseCatalogValue($strValue, $this->arrFields[$strFieldname], $arrNavigation);
            }

            $arrNavigationItems[$strType] = $arrNavigation;
        }

        $this->Template->prev = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['prev'];
        $this->Template->next = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['next'];
        $this->Template->items = $arrNavigationItems;
    }


    protected function getManuelNavigation($arrQuery, $blnVisibility, $arrTaxonomies)
    {

        $arrReturn = [];

        $arrQuery['where'][] = [
            [
                'field' => 'alias',
                'operator' => 'equal',
                'value' => $this->strAlias,
            ],
            [
                'field' => 'id',
                'operator' => 'equal',
                'value' => (int)$this->strAlias,
            ]
        ];

        $arrQuery['pagination'] = [

            'limit' => 1,
            'offset' => 0
        ];

        $objEntity = $this->SQLQueryBuilder->execute($arrQuery);

        $arrReturn['prev'] = $this->getNavigationItem((int)$objEntity->{$this->catalogBookNavigationItem}, false, $blnVisibility, $arrTaxonomies);
        $arrReturn['current'] = $objEntity->row();
        $arrReturn['next'] = $this->getNavigationItem((int)$objEntity->{$this->catalogBookNavigationItem}, true, $blnVisibility, $arrTaxonomies);

        return $arrReturn;
    }

    protected function getCustomNavigation($arrQuery)
    {

        $arrReturn = [];
        $objEntities = $this->SQLQueryBuilder->execute($arrQuery);

        if (!$objEntities->numRows) {

            return $arrReturn;
        }

        $arrRows = $objEntities->fetchAllAssoc();

        foreach ($arrRows as $intIndex => $arrRow) {

            if ($arrRow['alias'] == $this->strAlias || $arrRow['id'] == (int)$this->strAlias) {

                $arrReturn['prev'] = $intIndex > 0 ? $arrRows[$intIndex - 1] : [];
                $arrReturn['current'] = $arrRow;
                $arrReturn['next'] = isset($arrRows[$intIndex + 1]) ? $arrRows[$intIndex + 1] : [];

                break;
            }
        }

        return $arrReturn;
    }

    protected function getNavigationItem($numValue, $blnNext = true, $blnVisibility = false, $arrTaxonomies = [])
    {

        $arrQuery = [];
        $arrQuery['table'] = $this->catalogTablename;
        $arrQuery['where'] = $arrTaxonomies;

        if ($blnVisibility) {
            $this->addVisibilityQuery($arrQuery);
        }

        $arrQuery['orderBy'][] = [
            'field' => $this->catalogBookNavigationItem,
            'order' => $blnNext ? 'ASC' : 'DESC'
        ];

        $arrQuery['pagination'] = [
            'limit' => 1,
            'offset' => 0
        ];

        $arrQuery['where'][] = [
            'field' => $this->catalogBookNavigationItem,
            'operator' => $blnNext ? 'gt' : 'lt',
            'value' => $numValue
        ];

        $objEntity = $this->SQLQueryBuilder->execute($arrQuery);

        return $objEntity->numRows ? $objEntity->row() : [];
    }


    public function hasVisibility(): bool
    {

        if (!is_array($this->arrCatalog['operations'])) {
            return false;
        }

        if (!in_array('invisible', $this->arrCatalog['operations'])) {
            return false;
        }

        if (System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
            return false;
        }

        return true;
    }

    protected function addVisibilityQuery(&$arrQuery): void
    {

        $dteTime = Date::floorToMinute();

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

    protected function getMasterRedirect($arrCatalog = [], $strAlias = '')
    {

        if ($this->arrCatalog['useRedirect'] && $this->arrCatalog['internalUrlColumn']) {

            if ($arrCatalog[$this->arrCatalog['internalUrlColumn']]) {

                return Controller::replaceInsertTags($arrCatalog[$this->arrCatalog['internalUrlColumn']]);
            }
        }

        if ($this->arrCatalog['useRedirect'] && $this->arrCatalog['externalUrlColumn']) {

            if ($arrCatalog[$this->arrCatalog['externalUrlColumn']]) {

                return $arrCatalog[$this->arrCatalog['externalUrlColumn']];
            }
        }

        $strAlias = $this->getAliasWithParameters($strAlias, $arrCatalog);

        return $this->generateUrl($this->objMasterPage, $strAlias);
    }

    protected function getAliasWithParameters($strAlias, $arrCatalog = [])
    {

        if (!empty($this->arrRoutingParameter) && is_array($this->arrRoutingParameter)) {

            return Toolkit::generateAliasWithRouting($strAlias, $this->arrRoutingParameter, $arrCatalog);
        }

        return $strAlias;
    }

    protected function generateUrl($objPage, $strAlias)
    {

        if ($objPage == null) return '';

        return $objPage->getFrontendUrl(($strAlias ? '/' . $strAlias : ''));
    }
}