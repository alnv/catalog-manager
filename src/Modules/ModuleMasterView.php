<?php

namespace Alnv\CatalogManagerBundle\Modules;

use Alnv\CatalogManagerBundle\CatalogView;
use Alnv\CatalogManagerBundle\Elements\Entity;
use Alnv\CatalogManagerBundle\CatalogException;
use Contao\Module;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
use Contao\Controller;
use Symfony\Component\HttpFoundation\Request;
use Contao\BackendTemplate;

class ModuleMasterView extends Module
{

    protected $strAct = null;

    protected $strMasterAlias = null;

    protected $strTemplate = 'mod_catalog_master';


    public function generate()
    {

        if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {

            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->title = $this->headline;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            $objTemplate->wildcard = '### ' . strtoupper($GLOBALS['TL_LANG']['FMD']['catalogMasterView'][0]) . ' ###';

            return $objTemplate->parse();
        }

        if ($this->catalogCustomTemplate) $this->strTemplate = $this->catalogCustomTemplate;

        if (!Input::get('auto_item')) {
            return '';
        }

        System::loadLanguageFile('tl_module');

        $this->strMasterAlias = Input::get('auto_item');

        if (Input::get('pdf' . $this->id)) {
            $this->strAct = 'pdf';
        }

        return parent::generate();
    }


    protected function compile()
    {

        global $objPage;

        if ($this->strAct && $this->strAct == 'pdf') {
            $objEntity = new Entity(Input::get('pdf' . $this->id), $this->catalogTablename);
            $objEntity->getPdf($this->id, $this->catalogPdfTemplate);
        }

        $this->import(CatalogView::class);

        $arrQuery = [
            'where' => [
                [
                    [
                        'field' => 'alias',
                        'operator' => 'equal',
                        'value' => $this->strMasterAlias
                    ]
                ]
            ]
        ];

        if (is_numeric($this->strMasterAlias)) {
            $arrQuery['where'][0][] = [
                'field' => 'id',
                'operator' => 'equal',
                'value' => $this->strMasterAlias
            ];
        }

        $this->CatalogView->strMode = 'master';
        $this->CatalogView->arrOptions = $this->arrData;
        $this->CatalogView->objMainTemplate = $this->Template;
        $this->CatalogView->strTemplate = $this->catalogMasterTemplate ? $this->catalogMasterTemplate : 'catalog_master';
        $this->CatalogView->initialize();

        $strOutput = $this->CatalogView->getCatalogView($arrQuery);

        $this->Template->data = is_array($strOutput) ? $strOutput : [];
        $this->Template->output = is_string($strOutput) ? $strOutput : '';
        $this->CatalogView->getCommentForm($this->CatalogView->strMasterID);

        if (empty($strOutput)) {

            if ($this->catalogAutoRedirect && $this->catalogViewPage && $this->catalogViewPage != $objPage->id) {

                if ($objRedirect = PageModel::findByPk($this->catalogViewPage)) {
                    Controller::redirect($objRedirect->getFrontendUrl());
                }

                return null;
            }

            $objCatalogException = new CatalogException();
            $objCatalogException->set404();
        }

        if ($this->catalogSendJsonHeader) {

            $this->import('CatalogAjaxController');

            $this->CatalogAjaxController->setData([
                'data' => $this->Template->data,
                'output' => $this->Template->output,
                'showAsGroup' => $this->Template->showAsGroup,
            ]);

            $this->CatalogAjaxController->setType($this->catalogSendJsonHeader);
            $this->CatalogAjaxController->setModuleID($this->id);
            $this->CatalogAjaxController->sendJsonData();
        }
    }
}