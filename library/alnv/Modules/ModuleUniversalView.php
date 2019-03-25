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

        if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerBeforeInitializeView'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerBeforeInitializeView'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['catalogManagerBeforeInitializeView'] as $arrCallback )  {

                if ( is_array( $arrCallback ) ) {

                    $this->import( $arrCallback[0] );
                    $this->{$arrCallback[0]}->{$arrCallback[1]}( $this );
                }
            }
        }

        $this->strAct = \Input::get( 'act' . $this->id );
        $this->strMasterAlias = \Input::get( 'auto_item' );

        if ( !$this->strAct && \Input::get( 'pdf' . $this->id ) ) {

            $this->strAct = 'pdf';
        }

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

        if ( TL_MODE == 'FE' ) {

            if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerModifyMainTemplate'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerModifyMainTemplate'] ) ) {

                foreach ( $GLOBALS['TL_HOOKS']['catalogManagerModifyMainTemplate'] as $arrCallback )  {

                    if ( is_array( $arrCallback ) ) {

                        $this->import( $arrCallback[0] );
                        $this->strTemplate = $this->{$arrCallback[0]}->{$arrCallback[1]}( $this->strTemplate, $this );
                    }
                }
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

            case 'pdf':

                $this->downloadPdf();

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

        if ( !$this->FrontendEditing->checkPermission( $this->strAct ) || !$this->catalogEnableFrontendEditing ) {

            $objCatalogException = new CatalogException();
            $objCatalogException->set403();
        }

        if ( !$blnIsVisible ) {

            $objCatalogException = new CatalogException();
            $objCatalogException->set404();
        }

        $this->FrontendEditing->deleteEntity();
    }


    private function determineCatalogView() {

        $this->import( 'CatalogView' );
        $this->import( 'CatalogMessage' );

        $arrQuery = [

            'where' => [],
            'orderBy' => []
        ];
        
        $this->CatalogView->strMode = 'view';
        $this->CatalogView->arrOptions = $this->arrData;
        $this->CatalogView->objMainTemplate = $this->Template;
        $this->CatalogView->strTemplate = $this->catalogTemplate ? $this->catalogTemplate : 'catalog_teaser';
        $this->CatalogView->initialize();

        $this->Template->showAsGroup = $this->CatalogView->showAsGroup();
        $this->Template->message = $this->CatalogMessage->get( $this->id );
        $this->Template->createOperation = $this->CatalogView->getCreateOperation();

        $varView = $this->CatalogView->getCatalogView( $arrQuery );

        $this->Template->data = is_array( $varView ) ? $varView : [];
        $this->Template->map = $this->CatalogView->getMapViewOptions();
        $this->Template->output = is_string( $varView ) ? $varView : '';
        $this->Template->hasOperations = $this->CatalogView->getHasOperationFlag();

        if ( $this->Template->entityIndex[1] > 1 && $this->catalogShowQuantity ) $this->Template->message = sprintf( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['entitiesAmount'], $this->Template->entityIndex[1] );
        if ( $this->Template->entityIndex[1] == 1 && $this->catalogShowQuantity ) $this->Template->message = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['oneEntity'];
        if ( !$this->Template->entityIndex[1] ) $this->Template->message = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['noEntities'];

        if ( $this->catalogSendJsonHeader ) {

            $this->import( 'CatalogAjaxController' );

            $this->CatalogAjaxController->setData([

                'map' => $this->Template->map,
                'data' => $this->Template->data,
                'output' => $this->Template->output,
                'message' => $this->Template->message,
                'pagination' => $this->Template->pagination,
                'showAsGroup' => $this->Template->showAsGroup,
                'operations' => $this->Template->createOperation,
            ]);

            $this->CatalogAjaxController->setType( $this->catalogSendJsonHeader );
            $this->CatalogAjaxController->setModuleID( $this->id );
            $this->CatalogAjaxController->sendJsonData();
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

        $strOutput = $this->CatalogView->getCatalogView( $arrQuery );
        $this->CatalogView->getCommentForm( $this->CatalogView->strMasterID );

        if ( empty( $strOutput ) ) {

            if ( $this->catalogAutoRedirect && $this->catalogViewPage ) {

                \Controller::redirectToFrontendPage( $this->catalogViewPage );

                return null;
            }

            $objCatalogException = new CatalogException();
            $objCatalogException->set404();
        }

        $this->Template->showAsGroup = false;
        $this->Template->data = is_array( $strOutput ) ? $strOutput : [];
        $this->Template->output = is_string( $strOutput ) ? $strOutput : '';

        if ( $this->catalogSendJsonHeader ) {

            $this->import( 'CatalogAjaxController' );

            $this->CatalogAjaxController->setData([

                'data' => $this->Template->data,
                'output' => $this->Template->output,
                'showAsGroup' => $this->Template->showAsGroup,
            ]);

            $this->CatalogAjaxController->setType( $this->catalogSendJsonHeader );
            $this->CatalogAjaxController->setModuleID( $this->id );
            $this->CatalogAjaxController->sendJsonData();
        }
    }


    private function determineFormView() {

        $this->import( 'FrontendEditing' );

        $this->FrontendEditing->strTemplate = $this->catalogFormTemplate ? $this->catalogFormTemplate : 'form_catalog_default';
        $this->FrontendEditing->strItemID = \Input::get( 'id' . $this->id );
        $this->FrontendEditing->arrOptions = $this->arrData;
        $this->FrontendEditing->strAct = $this->strAct;
        $this->FrontendEditing->initialize();

        $blnIsVisible = $this->FrontendEditing->isVisible();

        if ( !$this->FrontendEditing->checkPermission( $this->strAct ) || !$this->catalogEnableFrontendEditing ) {

            $objCatalogException = new CatalogException();
            $objCatalogException->set403();
        }

        if ( !$blnIsVisible && $this->strAct != 'create' ) {

            $objCatalogException = new CatalogException();
            $objCatalogException->set404();
        }

        $this->Template->output = $this->FrontendEditing->render();
    }


    public function setOffset( $numOffset ) {

        $this->catalogOffset = $numOffset;
    }


    public function setPerPage( $numPerPage ) {

        $this->catalogPerPage = $numPerPage;
    }


    public function setPagination( $strPagination ) {

        $this->catalogAddPagination = $strPagination;
    }


    public function setTableView( $strTableView, $strFields ) {

        $this->enableTableView = $strTableView;
        $this->catalogActiveTableColumns = $strFields;

        if ( $this->catalogUseArray ) $this->catalogUseArray = '';
    }


    public function setFastMode( $strFastMode, $strFields ) {

        $this->catalogFastMode = $strFastMode;
        $this->catalogPreventFieldFromFastMode = $strFields;
    }


    public function setTemplate( $strTemplate ) {

        $this->catalogTemplate = $strTemplate;
        $this->catalogMasterTemplate = $strTemplate;
        $this->catalogTableBodyViewTemplate = $strTemplate;
    }


    public function setCss( $strClass ) {

        if ( is_array( $this->cssID ) && isset( $this->cssID[1] ) ) {

            $strId = $this->cssID[0];
            $strCss = $this->cssID[01];

            $strCss .= ( empty( $this->cssID[1] ) ? '' : ' ' ) . $strClass;

            $this->cssID = [ $strId, $strCss ];
        }
    }


    protected function downloadPdf() {

        $objEntity = new Entity( \Input::get( 'pdf' . $this->id ), $this->catalogTablename );
        $objEntity->getPdf( $this->id, $this->catalogPdfTemplate );
    }
}