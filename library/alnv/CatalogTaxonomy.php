<?php

namespace CatalogManager;

class CatalogTaxonomy extends CatalogController {


    public $arrOptions = [];
    public $strParameter = '';
    public $arrParameter = [];

    protected $arrCatalog = [];
    protected $arrCatalogFields = [];

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

            $this->arrParameter = Toolkit::getRoutingParameter( $this->strParameter, true );
        }

        if ( !$this->catalogTablename ) return null;

        $this->arrCatalog = $this->SQLQueryHelper->getCatalogByTablename( $this->catalogTablename );
        $this->arrCatalogFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogTablename( $this->catalogTablename );
    }


    public function getTaxonomiesView() {

        $strResults = '';
        $arrParameters = array_keys( $this->arrParameter );
        $objEntities = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( sprintf( 'SELECT %s FROM %s', implode( ',', $arrParameters ), $this->catalogTablename ) )->execute();

        if ( !$objEntities->numRows ) return '';

        while ( $objEntities->next() ) {

            $arrEntity =  $objEntities->row();

            if ( !empty( $arrEntity ) && is_array( $arrEntity ) ) {

                foreach ( $arrEntity as $strFieldname => $varValue ) {

                    if ( !$varValue ) continue;

                    if ( is_array( $this->arrParameter[ $strFieldname ] ) ) {

                        $varOptions = $this->parseCatalogValues( $varValue, $strFieldname, $arrEntity );

                        if ( is_array( $varOptions ) ) {

                            $varOptions = implode( ',', $varOptions );
                        }

                        $this->arrParameter[ $strFieldname ][ $varValue ] = [

                            'alias' => $varValue,
                            'title' => $varOptions
                        ];
                    }
                }
            }
        }

        // @todo

        return '';
    }


    public function parseCatalogValues( $varValue, $strFieldname, $arrCatalog ) {

        $arrField = $this->arrCatalogFields[ $strFieldname ];

        switch ( $arrField['type'] ) {

            case 'select':

                return Select::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'checkbox':

                return Checkbox::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'radio':

                return Radio::parseValue( $varValue, $arrField, $arrCatalog );

                break;
        }

        return $varValue;
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