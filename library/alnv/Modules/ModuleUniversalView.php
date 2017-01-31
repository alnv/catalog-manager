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

                default:

                    $this->determineCatalogView();

                    break;
            }
        }
    }

    private function determineCatalogView() {

        $this->import( 'CatalogView' );

        $arrQuery = [

            'where' => [],
            'orderBy' => [],
            'pagination' => []
        ];

        $this->CatalogView->arrOptions = $this->arrData;
        $this->CatalogView->strTemplate = $this->catalogTemplate ? $this->catalogTemplate : 'catalog_teaser';

        $this->CatalogView->initialize();

        $this->Template->output = $this->CatalogView->getCatalogView( $arrQuery );
        $this->Template->createOperation = $this->CatalogView->getCreateOperation();
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
            ],

            'orderBy' => [],
            'pagination' => []
        ];

        $this->CatalogView->arrOptions = $this->arrData;
        $this->CatalogView->strTemplate = $this->catalogMasterTemplate ? $this->catalogMasterTemplate : 'catalog_master';
        $this->CatalogView->initialize();

        $this->Template->output = $this->CatalogView->getCatalogView( $arrQuery );
    }

    private function determineFormView() {

        $this->import( 'FrontendEditing' );

        $this->FrontendEditing->arrOptions = $this->arrData;
        $this->FrontendEditing->strItemID = \Input::get( 'id' );
        $this->FrontendEditing->strAct =  \Input::get( 'act' . $this->id );

        $this->Template->output = $this->FrontendEditing->getCatalogFormByTablename( $this->strCatalogTable );
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