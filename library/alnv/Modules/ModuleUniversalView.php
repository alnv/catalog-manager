<?php

namespace CatalogManager;

class ModuleUniversalView extends \Module {

    private $strMasterAlias;
    private $strCatalogTable;

    protected $strTemplate = 'mod_catalog_view';

    public function generate() {

        if ( TL_MODE == 'BE' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . $this->name . ' ###';
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    protected function compile() {

        $this->strMasterAlias = \Input::get( 'auto_item' );
        $this->catalogJoinFields = Toolkit::parseStringToArray( $this->catalogJoinFields );
        $this->catalogRelatedChildTables = Toolkit::parseStringToArray( $this->catalogRelatedChildTables );

        $this->setTable(); // @todo settings

        if ( $this->strMasterAlias && !$this->catalogPreventMasterView ) {

            $this->determineMasterView();
        }

        else {

            switch ( \Input::get( 'act' . $this->id ) ) {

                case 'create':
                case 'copy':
                case 'edit':

                    $this->determineFormView();

                    break;


                case 'delete':

                    $this->deleteItemFromCatalog();

                    break;

                default:

                    $this->determineCatalogView();

                    break;
            }
        }
    }

    private function deleteItemFromCatalog() {

        $this->import( 'FrontendEditing' );

        $this->FrontendEditing->arrOptions = $this->arrData;
        $this->FrontendEditing->strItemID = \Input::get( 'id' );
        $this->FrontendEditing->strAct = \Input::get( 'act' . $this->id );
        $this->FrontendEditing->strTemplate = $this->catalogFormTemplate ? $this->catalogFormTemplate : 'form_catalog_default';
        $this->FrontendEditing->initialize();

        $this->FrontendEditing->deleteEntity();
    }

    private function determineCatalogView() {

        $this->import( 'CatalogView' );

        $arrQuery = [

            'where' => [],

            'orderBy' => [

                [
                    'order' => 'ASC',
                    'field' => 'alias'
                ]
            ],

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

        $blnHasPermission = $this->CatalogView->checkPermission();

        $this->Template->output = $blnHasPermission ? $this->CatalogView->getCatalogView( $arrQuery ) : '';
        $this->Template->createOperation = $this->CatalogView->getCreateOperation();

        if ( !$blnHasPermission ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate( $this->CatalogView->arrViewPage['id'] );
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

        $blnHasPermission = $this->CatalogView->checkPermission();

        $this->Template->output = $blnHasPermission ? $this->CatalogView->getCatalogView( $arrQuery ) : '';

        if ( !$blnHasPermission ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate( $this->CatalogView->arrMasterPage['id'] );
        }
    }

    private function determineFormView() {

        $this->import( 'FrontendEditing' );
        
        $this->FrontendEditing->arrOptions = $this->arrData;
        $this->FrontendEditing->strItemID = \Input::get( 'id' );
        $this->FrontendEditing->strAct = \Input::get( 'act' . $this->id );
        $this->FrontendEditing->strTemplate = $this->catalogFormTemplate ? $this->catalogFormTemplate : 'form_catalog_default';
        $this->FrontendEditing->initialize();

        $blnHasPermission = $this->FrontendEditing->checkPermission();

        $this->Template->output = $blnHasPermission ? $this->FrontendEditing->getCatalogForm() : '';

        if ( !$blnHasPermission ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate( $this->FrontendEditing->strPageID );
        }
    }

    private function setTable() {

        $strTable = \Input::get( 'table' );

        $this->strCatalogTable = $this->catalogTablename;

        if ( $strTable && $this->Database->tableExists( $strTable ) ) {

            if ( in_array( $strTable, $this->catalogRelatedChildTables ) || $strTable == $this->catalogRelatedParentTable ) {

                $this->strCatalogTable = $strTable;
            }
        }
    }
}