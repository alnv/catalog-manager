<?php

namespace CatalogManager;

class CatalogTaxonomy extends CatalogController {


    public $arrOptions = [];

    protected $strName = '';
    protected $arrActive = [];
    protected $arrCatalog = [];
    protected $strParameter = '';
    protected $arrParameter = [];
    protected $arrTaxonomyTree = [];
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

            $this->arrParameter = array_keys( Toolkit::getRoutingParameter( $this->strParameter ) );
        }

        if ( !$this->catalogTablename ) return null;

        $this->arrCatalog = $this->SQLQueryHelper->getCatalogByTablename( $this->catalogTablename );
        $this->arrCatalogFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogTablename( $this->catalogTablename );

        $this->setTaxonomyTree();
    }


    public function getTaxonomyTree( $strTree = 'arrTaxonomyTree' ) {

        $strResults = '';
        $arrTaxonomies = [];

        if ( !isset( $this->{$strTree} ) || empty( $this->{$strTree} ) || !is_array( $this->{$strTree} ) ) {

            return $strResults;
        }

        foreach ( $this->{$strTree} as $arrTree ) {

            $arrTaxonomies[] = $this->setSubTaxonomies( $arrTree );
        }

        $objTemplate = new \FrontendTemplate( 'ctlg_taxonomy_nav' );
        $objTemplate->setData(['arrItems' => $arrTaxonomies ]);
        $strResults .= $objTemplate->parse();

        return $strResults;
    }


    protected function setSubTaxonomies( $arrTree ) {

        if ( ( $arrTree['active'] && $arrTree['next'] ) && ( isset( $this->{$arrTree['next']} ) && is_array( $this->{$arrTree['next']} ) ) ) {

           $arrTree['subItems'] = $this->getTaxonomyTree( $arrTree['next'] );
        }

        return $arrTree;
    }


    protected function setTaxonomyTree() {

        global $objPage;

        $strTaxonomyUrl = '';
        $strTempParameter = '';

        foreach ( $this->arrParameter as $intIndex => $strParameter ) {

            $strNextParameter = $this->arrParameter[ $intIndex + 1 ] ? $this->arrParameter[ $intIndex + 1 ] : '';

            if ( !$intIndex ) {

                $objEntities = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( sprintf( 'SELECT DISTINCT %s FROM %s', $strParameter, $this->catalogTablename ) )->execute();

                if ( !$objEntities->numRows ) continue;

                while ( $objEntities->next() ) {

                    if ( !$objEntities->{$strParameter} ) continue;

                    $varOptions = $this->parseCatalogValues( $objEntities->{$strParameter}, $strParameter, [] );

                    if ( is_array( $varOptions ) ) $varOptions = implode( ',', $varOptions );

                    $this->arrTaxonomyTree[ $objEntities->{$strParameter} ] = [

                        'title' => $varOptions,
                        'next' => $strNextParameter,
                        'alias' => $objEntities->{$strParameter},
                        'href' => $this->generateUrl( $objPage, '/' . $objEntities->{$strParameter} ),
                        'class' => \Input::get( $strParameter ) == $objEntities->{$strParameter} ? 'active' : '',
                        'active' => \Input::get( $strParameter ) == $objEntities->{$strParameter} ? true : false,
                    ];
                }

                $this->strName = $strParameter;
            }

            if ( $intIndex || ( \Input::get( $strTempParameter ) && $strTempParameter ) ) {

                $objEntities = $this->SQLQueryHelper->SQLQueryBuilder->Database
                    ->prepare( sprintf( 'SELECT DISTINCT %s FROM %s WHERE FIND_IN_SET( ?, LOWER( CAST( %s AS CHAR ) ) ) AND FIND_IN_SET( ?, LOWER( CAST( %s AS CHAR ) ) ) ', $strParameter, $this->catalogTablename, $strTempParameter, $this->strName ) )
                    ->execute( \Input::get( $strTempParameter ), \Input::get( $this->strName ) );

                if ( !$objEntities->numRows ) continue;

                if ( !isset( $this->{$strParameter} ) ) $this->{$strParameter} = [];

                $strTaxonomyUrl .= $strTaxonomyUrl ? '/' . \Input::get( $strTempParameter ) : '/' . \Input::get( $strTempParameter );

                while ( $objEntities->next() ) {

                    if ( !$objEntities->{$strParameter} ) continue;

                    $varOptions = $this->parseCatalogValues( $objEntities->{$strParameter}, $strParameter, [] );

                    if ( is_array( $varOptions ) ) $varOptions = implode( ',', $varOptions );

                    $this->{$strParameter}[] = [

                        'title' => $varOptions,
                        'next' => $strNextParameter,
                        'alias' => $objEntities->{$strParameter},
                        'class' => \Input::get( $strParameter ) == $objEntities->{$strParameter} ? 'active' : '',
                        'active' => \Input::get( $strParameter ) == $objEntities->{$strParameter} ? true : false,
                        'href' =>  $this->generateUrl( $objPage, $strTaxonomyUrl . '/' . $objEntities->{$strParameter} ),
                    ];
                }
            }

            $strTempParameter = $strParameter;

            if ( \Input::get( $strParameter ) ) $this->arrActive[] = $strParameter;
        }
    }


    protected function generateUrl( $objPage, $strAlias ) {

        if ( $objPage == null ) return '';

        return $this->generateFrontendUrl( $objPage->row(), $strAlias );
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

        if ( $objPage->catalogUseRouting ) {

            $this->strParameter = str_replace( '{auto_item}', '', $objPage->catalogRouting );
        }

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