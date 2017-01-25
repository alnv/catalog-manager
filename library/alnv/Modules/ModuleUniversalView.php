<?php

namespace CatalogManager;

class ModuleUniversalView extends \Module {

    private $strCatalogID;

    private $arrCatalog = [];

    private $arrCatalogFields =[];

    protected $strTemplate = 'mod_catalog_view';

    public function generate() {

        if ( TL_MODE == 'BE' ) {

            //
        }

        return parent::generate();
    }

    protected function compile() {

        $strAutoItem = \Input::get( 'auto_item' );

        $this->CatalogView = new CatalogView();

        $this->arrCatalog = $this->CatalogView->getCatalogByTablename( $this->catalogTablename );

        $this->strCatalogID = $this->arrCatalog['id'];

        $this->arrCatalogFields = $this->CatalogView->getCatalogFieldsByCatalogID( $this->strCatalogID );

        if ( $strAutoItem ) {

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

                    $this->cdetermineCatalogView();

                    break;
            }
        }
    }

    private function cdetermineCatalogView() {

        $arrCatalogs = $this->CatalogView->getCatalogDataByTable( $this->catalogTablename, [] );

        //
    }

    private function determineMasterView() {

        $arrCatalogs = $this->CatalogView->getCatalogDataByTable( $this->catalogTablename, [] );

        //
    }

    private function determineEditFormView() {

        $arrCatalogs = $this->CatalogView->getCatalogDataByTable( $this->catalogTablename, [] );

        //
    }

    private function determineCreateFormView() {

        //
    }
}