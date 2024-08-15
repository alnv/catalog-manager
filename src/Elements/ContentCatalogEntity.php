<?php

namespace Alnv\CatalogManagerBundle\Elements;

use Contao\ContentElement;
use Contao\BackendTemplate;
use Contao\PageModel;
use Alnv\CatalogManagerBundle\Toolkit;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class ContentCatalogEntity extends ContentElement
{

    protected array $arrFields = [];
    protected array $arrCatalog = [];
    protected $strTemplate = 'ce_catalog_entity';


    public function generate()
    {

        if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . \strtoupper($GLOBALS['TL_LANG']['CTE']['catalogCatalogEntity'][0]) . ' ###';
            return $objTemplate->parse();
        }

        $this->catalogEntityId = $this->catalogEntityId ?: '0';

        if (!$this->catalogTablename || !$this->catalogEntityId) {
            return '';
        }

        if (!$this->Database->tableExists($this->catalogTablename)) {
            return '';
        }

        if ($this->catalogEntityTemplate) {
            $this->strTemplate = $this->catalogEntityTemplate;
        }

        return parent::generate();
    }

    protected function compile()
    {

        $objEntity = new Entity($this->catalogEntityId, $this->catalogTablename);
        $arrEntity = $objEntity->getEntity();

        foreach ($arrEntity as $strFieldname => $strValue) {

            $this->Template->{$strFieldname} = $strValue;
        }

        $strMasterUrl = '';

        if ($this->catalogRedirectType) {

            switch ($this->catalogRedirectType) {

                case 'internal':
                    $objPage = $this->getPage();
                    if ($objPage !== null) {
                        $strMasterUrl = $objPage->getFrontendUrl();
                    }
                    $this->catalogRedirectTarget = '';
                    break;

                case 'master':
                    $objPage = $this->getPage();
                    if ($objPage !== null) {
                        $strMasterUrl = $objPage->getFrontendUrl(($arrEntity['alias'] ? '/' . $arrEntity['alias'] : ''));
                    }
                    $this->catalogRedirectTarget = '';
                    break;
                case 'link':
                    $strMasterUrl = Toolkit::replaceInsertTags($this->catalogRedirectUrl);

                    break;
            }
        }

        $this->Template->masterUrl = $strMasterUrl;
        $this->Template->masterUrlText = $this->getLinkText();
        $this->Template->fields = $objEntity->getTemplateFields();
        $this->Template->masterUrlTarget = $this->catalogRedirectTarget;
        $this->Template->masterUrlTitle = Toolkit::replaceInsertTags($this->catalogRedirectTitle);
    }

    protected function getPage()
    {

        if (!$this->catalogRedirectPage) {
            return null;
        }

        return PageModel::findByPK($this->catalogRedirectPage);
    }

    protected function getLinkText()
    {

        $strText = Toolkit::replaceInsertTags($this->catalogRedirectText);
        if ($strText) {
            return $strText;
        }
        return $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['detailLink'];
    }
}