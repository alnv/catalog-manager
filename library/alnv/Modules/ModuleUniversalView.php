<?php

namespace CatalogManager;

class ModuleUniversalView extends \Module {

    private $strMasterAlias;
    protected $strTemplate = 'mod_catalog_view';

    public function generate() {

        if ( TL_MODE == 'BE' ) {

            //
        }

        return parent::generate();
    }

    protected function compile() {

        $this->import( 'CatalogView' );

        $this->strMasterAlias = \Input::get( 'auto_item' );

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

        $arrView = [

            'useTemplate' => true,
            'masterPage' => $this->catalogMasterPage,
            'useMasterPage' => $this->catalogUseMasterPage ? true : false,
            'joins' => Toolkit::parseStringToArray( $this->catalogRelation ),
            'template' =>  $this->catalogTemplate ? $this->catalogTemplate : 'catalog_teaser'
        ];

        $arrQuery = [

            'where' => [],
            'orderBy' => [],
            'pagination' => []
        ];

        $arrCatalogs = $this->CatalogView->getCatalogDataByTable( $this->catalogTablename, $arrView, $arrQuery );

        $this->Template->catalogs = $arrCatalogs['view'];
    }

    private function determineMasterView() {

        $arrView = [

            'useTemplate' => true,
            'viewPage' => $this->catalogViewPage,
            'useViewPage' => $this->catalogUseViewPage ? true : false,
            'joins' => Toolkit::parseStringToArray( $this->catalogRelation ),
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

        $arrCatalogs = $this->CatalogView->getCatalogDataByTable( $this->catalogTablename, $arrView, $arrQuery );

        $this->Template->catalogs = $arrCatalogs['view'];
    }

    private function determineEditFormView() {
        
        //
    }

    private function determineCreateFormView() {

        //
    }
}