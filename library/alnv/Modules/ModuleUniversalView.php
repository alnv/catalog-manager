<?php

namespace CatalogManager;

class ModuleUniversalView extends \Module {


    protected $strAct;
    protected $strMasterAlias;
    protected $strTemplate = 'mod_catalog_universal';


    public function generate() {

        if ( TL_MODE == 'BE' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->title = $this->headline;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            $objTemplate->wildcard = '### ' . utf8_strtoupper( $GLOBALS['TL_LANG']['FMD']['catalogUniversalView'][0] ) . ' ###';

            return $objTemplate->parse();
        }

        $this->strAct = \Input::get( 'act' . $this->id );
        $this->strMasterAlias = \Input::get( 'auto_item' );

        if ( TL_MODE == 'FE' && $this->catalogCustomTemplate ) {

            $this->strTemplate = $this->catalogCustomTemplate;
        }

        if ( TL_MODE == 'FE' && $this->catalogUseMap && !$this->strAct ) {

            $this->strTemplate = $this->catalogMapViewTemplate;
        }

        if ( TL_MODE == 'FE' && $this->enableTableView && !$this->strAct ) {

            if ( !$this->strMasterAlias || $this->catalogPreventMasterView ) {

                $this->strTemplate = $this->catalogTableViewTemplate;
            }
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

                $this->deleteEntityFromCatalog();

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


    private function deleteEntityFromCatalog() {

        $this->import( 'FrontendEditing' );

        $this->FrontendEditing->strAct = $this->strAct;
        $this->FrontendEditing->arrOptions = $this->arrData;
        $this->FrontendEditing->strItemID = \Input::get( 'id' . $this->id );
        $this->FrontendEditing->strTemplate = $this->catalogFormTemplate ? $this->catalogFormTemplate : 'form_catalog_default';
        $this->FrontendEditing->initialize();

        $blnIsVisible = $this->FrontendEditing->isVisible();
        $blnHasPermission = $this->FrontendEditing->checkPermission( $this->strAct );

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
        $this->Template->showAsGroup = $this->CatalogView->showAsGroup();
        $this->Template->createOperation = $this->CatalogView->getCreateOperation();

        $varView = $this->CatalogView->getCatalogView( $arrQuery );

        $this->Template->data = is_array( $varView ) ? $varView : [];
        $this->Template->output = is_string( $varView ) ? $varView : '';

        if ( empty( $varView ) ) {

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
        
        if ( !$this->CatalogView->checkPermission() ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate( $this->CatalogView->arrMasterPage['id'] );

            return null;
        }

        $strOutput = $this->CatalogView->getCatalogView( $arrQuery );
        $this->CatalogView->getCommentForm( $this->CatalogView->strMasterID );

        if ( empty( $strOutput ) ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate( $this->CatalogView->strPageID );

            return null;
        }

        $this->Template->showAsGroup = false;
        $this->Template->data = is_array( $strOutput ) ? $strOutput : [];
        $this->Template->output = is_string( $strOutput ) ? $strOutput : '';
    }


    private function determineFormView() {

        $this->import( 'FrontendEditing' );

        $this->FrontendEditing->strAct = $this->strAct;
        $this->FrontendEditing->arrOptions = $this->arrData;
        $this->FrontendEditing->strItemID = \Input::get( 'id' . $this->id );
        $this->FrontendEditing->strTemplate = $this->catalogFormTemplate ? $this->catalogFormTemplate : 'form_catalog_default';
        $this->FrontendEditing->initialize();

        $blnIsVisible = $this->FrontendEditing->isVisible();
        $blnHasAccess = $this->FrontendEditing->checkAccess();
        $blnHasPermission = $this->FrontendEditing->checkPermission( $this->strAct );

        if ( !$blnHasPermission || !$blnHasAccess ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate( $this->FrontendEditing->strPageID );

            return null;
        }

        if ( !$blnIsVisible && $this->strAct != 'create' ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate( $this->FrontendEditing->strPageID );

            return null;
        }

        $this->Template->output = $this->FrontendEditing->getCatalogForm();
    }
}