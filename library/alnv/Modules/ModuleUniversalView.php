<?php

namespace CatalogManager;

class ModuleUniversalView extends \Module {

    private $strCatalogTable;

    private $strMasterAlias;

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

            $strMode = \Input::get( 'mode' . $this->id );

            switch ( $strMode ) {

                case 'edit':

                    $this->determineEditFormView();

                    break;

                case 'create' :

                    $this->determineCreateFormView();

                    break;

                default:

                    $this->determineCatalogView();

                    break;
            }
        }
    }

    private function determineCatalogView() {

        $this->import( 'CatalogView' );

        $arrView = [

            'useTemplate' => true,
            'joins' => $this->catalogJoinFields,
            'masterPage' => $this->catalogMasterPage,
            'pTable' => $this->catalogRelatedParentTable,
            'cTables' => $this->catalogRelatedChildTables,
            'joinPTable' => $this->catalogJoinParentTable ? true : false,
            'useMasterPage' => $this->catalogUseMasterPage ? true : false,
            'template' =>  $this->catalogTemplate ? $this->catalogTemplate : 'catalog_teaser'
        ];

        $arrQuery = [

            'where' => [],
            'orderBy' => [],
            'pagination' => []
        ];

        $arrCatalogs = $this->CatalogView->getCatalogDataByTable( $this->strCatalogTable, $arrView, $arrQuery );

        $this->Template->catalogs = $arrCatalogs['view'];
    }

    private function determineMasterView() {

        $this->import( 'CatalogView' );

        $arrView = [

            'useTemplate' => true,
            'joins' => $this->catalogJoinFields,
            'viewPage' => $this->catalogViewPage,
            'pTable' => $this->catalogRelatedParentTable,
            'cTables' => $this->catalogRelatedChildTables,
            'useViewPage' => $this->catalogUseViewPage ? true : false,
            'joinPTable' => $this->catalogJoinParentTable ? true : false,
            'template' =>  $this->catalogMasterTemplate ? $this->catalogMasterTemplate : 'catalog_master'
        ];

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

        $arrCatalogs = $this->CatalogView->getCatalogDataByTable( $this->strCatalogTable, $arrView, $arrQuery );

        $this->Template->output = $arrCatalogs['view'];
    }

    private function determineEditFormView() {

        $this->import( 'FrontendEditing' );

        $this->Template->output = $this->FrontendEditing->getCatalogFormByTablename( $this->strCatalogTable );
    }

    private function determineCreateFormView() {

        $this->import( 'FrontendEditing' );

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