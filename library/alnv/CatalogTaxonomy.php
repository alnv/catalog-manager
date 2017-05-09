<?php

namespace CatalogManager;

class CatalogTaxonomy extends CatalogController {


    public $arrOptions = [];
    public $strParameter = '';
    public $arrParameter = [];


    public function __construct() {

        parent::__construct();

        $this->import( 'SQLQueryHelper' );
    }


    public function initialize() {

        $this->setOptions();

        switch ( $this->catalogRoutingSource ) {

            case 'page':

                $this->getParameterFromPage();

                break;

            case 'module':

                $this->getParameterFromModule();

                break;
        }

        $this->strParameter = preg_replace( '{/$}', '', $this->strParameter );

        if ( $this->strParameter ) {

            $this->arrParameter = Toolkit::getRoutingParameter( $this->strParameter );
        }
    }


    protected function getParameterFromPage() {

        if ( !$this->catalogPageRouting ) return null;

        $objPage = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( 'SELECT * FROM tl_page WHERE id = ?' )->limit(1)->execute( $this->catalogPageRouting );

        if ( !$objPage->numRows ) return null;

        $this->strParameter = str_replace( '{auto_item}', '', $objPage->catalogRouting );
        $this->catalogTablename = $objPage->catalogRoutingTable;
    }


    protected function getParameterFromModule() {

        $arrRoutingSchema = [];
        $arrCatalogRoutingParameter = Toolkit::deserialize( $this->catalogRoutingParameter );

        if ( !empty( $arrCatalogRoutingParameter ) && is_array( $arrCatalogRoutingParameter ) ) {

            foreach ( $arrCatalogRoutingParameter as $arrParameter ) {

                if ( $arrParameter ) {

                    $arrRoutingSchema[] = '{' . $arrParameter . '}';
                }
            }
        }

        if ( !empty( $arrRoutingSchema ) && is_array( $arrRoutingSchema ) ) {

            $this->strParameter = implode( '/', $arrRoutingSchema );
        }
    }


    protected function setOptions() {

        if ( !empty( $this->arrOptions ) && is_array( $this->arrOptions ) ) {

            foreach ( $this->arrOptions as $strKey => $varValue ) {

                $this->{$strKey} = $varValue;
            }
        }
    }
}