<?php

namespace CatalogManager;

class ModuleUniversalView extends \Module {


    protected $strAct;
    protected $strMasterAlias;
    protected $strTemplate = 'mod_catalog_universal';


    public function generate() {

        if ( TL_MODE == 'BE' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . $this->name . ' ###';
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->strAct = \Input::get( 'act' . $this->id );
        $this->strMasterAlias = \Input::get( 'auto_item' );

        if ( TL_MODE == 'FE' && $this->catalogUseMap && !$this->strMasterAlias ) {

            $this->strTemplate = $this->catalogMapViewTemplate;
        }

        return parent::generate();
    }


    protected function compile() {

        switch ( $this->strAct  ) {

            case 'create':
            case 'copy':
            case 'edit':

                $this->determineFormView();

                break;


            case 'delete':

                $this->deleteItemFromCatalog();

                break;

            default:

                if ( $this->strMasterAlias && !$this->catalogPreventMasterView ) {

                    $this->determineMasterView();
                }

                else {

                    $this->determineCatalogView();
                }

                break;
        }
    }


    private function deleteItemFromCatalog() {

        $this->import( 'FrontendEditing' );

        $this->FrontendEditing->strAct = $this->strAct;
        $this->FrontendEditing->arrOptions = $this->arrData;
        $this->FrontendEditing->strItemID = \Input::get( 'id' );
        $this->FrontendEditing->strTemplate = $this->catalogFormTemplate ? $this->catalogFormTemplate : 'form_catalog_default';
        $this->FrontendEditing->initialize();

        $this->FrontendEditing->deleteEntity();
    }


    private function determineCatalogView() {

        $this->import( 'CatalogView' );

        $arrQuery = [

            'where' => [],
            'orderBy' => [],

            'pagination' => [

                'limit' => $this->catalogPerPage,
                'offset' => $this->catalogOffset
            ]
        ];
        
        $this->CatalogView->strMode = 'view';
        $this->CatalogView->arrOptions = $this->arrData;
        $this->CatalogView->objMainTemplate = $this->Template;
        $this->CatalogView->strTemplate = $this->catalogTemplate ? $this->catalogTemplate : 'catalog_teaser';
        $this->CatalogView->initialize();

        if ( !$this->CatalogView->checkPermission() ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate( $this->CatalogView->arrViewPage['id'] );

            return null;
        }

        $this->Template->message = '';
        $this->Template->map = $this->CatalogView->getMapViewOptions();
        $this->Template->output = $this->CatalogView->getCatalogView( $arrQuery );
        $this->Template->createOperation = $this->CatalogView->getCreateOperation();

        if ( !$this->Template->output ) {

            $this->Template->message = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['noEntities'];
        }
    }


    private function determineMasterView() {

        $this->import( 'CatalogView' );

        $arrQuery = [

            'where' => [

                [
                    [
                        'field' => 'id',
                        'operator' => 'equal',
                        'value' => $this->strMasterAlias
                    ],

                    [
                        'field' => 'alias',
                        'operator' => 'equal',
                        'value' => $this->strMasterAlias
                    ]
                ]
            ]
        ];

        $this->CatalogView->strMode = 'master';
        $this->CatalogView->arrOptions = $this->arrData;
        $this->CatalogView->objMainTemplate = $this->Template;
        $this->CatalogView->strTemplate = $this->catalogMasterTemplate ? $this->catalogMasterTemplate : 'catalog_master';
        $this->CatalogView->initialize();
        $this->CatalogView->getCommentForm();

        if ( !$this->CatalogView->checkPermission() ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate( $this->CatalogView->arrMasterPage['id'] );

            return null;
        }

        $this->Template->output = $this->CatalogView->getCatalogView( $arrQuery );

        if ( !$this->Template->output ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate( $this->CatalogView->strPageID );

            return null;
        }
    }


    private function determineFormView() {

        $this->import( 'FrontendEditing' );

        $this->FrontendEditing->strAct = $this->strAct;
        $this->FrontendEditing->arrOptions = $this->arrData;
        $this->FrontendEditing->strItemID = \Input::get( 'id' );
        $this->FrontendEditing->strTemplate = $this->catalogFormTemplate ? $this->catalogFormTemplate : 'form_catalog_default';
        $this->FrontendEditing->initialize();

        $blnIsVisible = $this->FrontendEditing->isVisible();
        $blnHasPermission = $this->FrontendEditing->checkPermission();

        if ( !$blnHasPermission ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate( $this->FrontendEditing->strPageID );

            return null;
        }

        if ( !$blnIsVisible ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate( $this->FrontendEditing->strPageID );

            return null;
        }

        $this->Template->output = $this->FrontendEditing->getCatalogForm();
    }
}