<?php

namespace CatalogManager;


class ModuleCatalogBookNavigation extends \Module {


    protected $arrFields = [];
    protected $strAlias = null;
    protected $arrCatalog = [];
    protected $objMasterPage = null;
    protected $arrRoutingParameter = [];
    protected $strTemplate = 'mod_catalog_book_navigation';


    public function generate() {

        if ( TL_MODE == 'BE' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');

            //

            return $objTemplate->parse();

        }

        $this->strAlias = \Input::get('auto_item');

        if ( !$this->strAlias ) {

            return null;
        }

        return parent::generate();
    }


    protected function compile() {

        $this->import('SQLQueryBuilder');

        $this->catalogBookNavigationItem = 'num';

        if ( $this->catalogMasterPage ) {

            $this->objMasterPage = \PageModel::findByPk( $this->catalogMasterPage );
        }

        $arrQuery = [];
        $arrTaxonomies = [];
        $arrNavigationItems = [];
        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize( $this->catalogTablename );

        $this->arrCatalog = $objCatalogFieldBuilder->getCatalog();
        $this->arrFields = $objCatalogFieldBuilder->getCatalogFields();
        $this->catalogTaxonomies = Toolkit::deserialize( $this->catalogTaxonomies );

        $arrQuery['table'] = $this->catalogTablename;
        $arrQuery['where'] = [];

        if ( !empty( $this->catalogTaxonomies['query'] ) && is_array( $this->catalogTaxonomies['query'] ) && $this->catalogUseTaxonomies ) {

            $arrTaxonomies = Toolkit::parseQueries( $this->catalogTaxonomies['query'] );
        }

        $blnVisibility = $this->hasVisibility();

        if ( $blnVisibility ) {

            $this->addVisibilityQuery( $arrQuery );
        }

        $arrQuery['where'][] = [
            [
                'field' => 'alias',
                'operator' => 'equal',
                'value' => $this->strAlias,
            ],
            [
                'field' => 'id',
                'operator' => 'equal',
                'value' => (int) $this->strAlias,
            ]
        ];

        $arrQuery['pagination'] = [

            'limit' => 1,
            'offset' => 0
        ];

        $objEntity = $this->SQLQueryBuilder->execute( $arrQuery );

        $arrNavigationItems['prev'] = $this->getNavigationItem( (int) $objEntity->{$this->catalogBookNavigationItem}, false, $blnVisibility, $arrTaxonomies );
        $arrNavigationItems['current'] = $objEntity->row();
        $arrNavigationItems['next'] = $this->getNavigationItem( (int) $objEntity->{$this->catalogBookNavigationItem}, true, $blnVisibility, $arrTaxonomies );

        foreach ( $arrNavigationItems as $strType => $arrNavigation ) {

            if ( empty( $arrNavigation ) ) {

                unset( $arrNavigationItems[ $strType ] );

                continue;
            }

            $arrNavigation['origin'] = $arrNavigation;
            $arrNavigation['masterUrl'] = $this->getMasterRedirect( $arrNavigation, $arrNavigation['alias'] );

            foreach ( $arrNavigation as $strFieldname => $strValue ) {

                $arrNavigation[ $strFieldname ] = Toolkit::parseCatalogValue( $strValue, $this->arrFields[ $strFieldname ], $arrNavigation );
            }

            $arrNavigationItems[ $strType ] = $arrNavigation;
        }

        $this->Template->items = $arrNavigationItems;
    }


    protected function getNavigationItem( $numValue, $blnNext = true, $blnVisibility = false, $arrTaxonomies = [] ) {

        $arrQuery = [];
        $arrQuery['table'] = $this->catalogTablename;
        $arrQuery['where'] = $arrTaxonomies;

        if ( $blnVisibility ) {

            $this->addVisibilityQuery( $arrQuery );
        }

        $arrQuery['orderBy'][] = [

            'field' => $this->catalogBookNavigationItem,
            'order' => $blnNext ? 'ASC' : 'DESC'
        ];

        $arrQuery['pagination'] = [

            'limit' => 1,
            'offset' => 0
        ];

        $arrQuery['where'][] = [

            'field' => $this->catalogBookNavigationItem,
            'operator' => $blnNext ? 'gt' : 'lt',
            'value' => $numValue
        ];

        $objEntity = $this->SQLQueryBuilder->execute( $arrQuery );
        
        return $objEntity->numRows ? $objEntity->row() : [];
    }


    public function hasVisibility() {

        if ( !is_array( $this->arrCatalog['operations'] ) ) {

            return false;
        }

        if ( !in_array( 'invisible', $this->arrCatalog['operations'] ) ) {

            return false;
        }

        if ( BE_USER_LOGGED_IN ) {

            return false;
        }

        return true;
    }


    protected function addVisibilityQuery( &$arrQuery ) {

        $dteTime = \Date::floorToMinute();

        $arrQuery['where'][] = [

            'field' => 'tstamp',
            'operator' => 'gt',
            'value' => 0
        ];

        $arrQuery['where'][] = [

            [
                'value' => '',
                'field' => 'start',
                'operator' => 'equal'
            ],

            [
                'field' => 'start',
                'operator' => 'lte',
                'value' => $dteTime
            ]
        ];

        $arrQuery['where'][] = [

            [
                'value' => '',
                'field' => 'stop',
                'operator' => 'equal'
            ],

            [
                'field' => 'stop',
                'operator' => 'gt',
                'value' => $dteTime
            ]
        ];

        $arrQuery['where'][] = [

            'field' => 'invisible',
            'operator' => 'not',
            'value' => '1'
        ];
    }


    protected function getMasterRedirect( $arrCatalog = [], $strAlias = '' ) {

        if ( $this->arrCatalog['useRedirect'] && $this->arrCatalog['internalUrlColumn'] ) {

            if ( $arrCatalog[ $this->arrCatalog['internalUrlColumn'] ] ) {

                return \Controller::replaceInsertTags( $arrCatalog[ $this->arrCatalog['internalUrlColumn'] ] );
            }
        }

        if ( $this->arrCatalog['useRedirect'] && $this->arrCatalog['externalUrlColumn'] ) {

            if ( $arrCatalog[ $this->arrCatalog['externalUrlColumn'] ] ) {

                return $arrCatalog[ $this->arrCatalog['externalUrlColumn'] ];
            }
        }

        $strAlias = $this->getAliasWithParameters( $strAlias, $arrCatalog );

        return $this->generateUrl( $this->objMasterPage, $strAlias );
    }


    protected function getAliasWithParameters( $strAlias, $arrCatalog = [] ) {

        if ( !empty( $this->arrRoutingParameter ) && is_array( $this->arrRoutingParameter ) ) {

            return Toolkit::generateAliasWithRouting( $strAlias, $this->arrRoutingParameter, $arrCatalog );
        }

        return $strAlias;
    }


    protected function generateUrl( $objPage, $strAlias ) {

        if ( $objPage == null ) return '';

        return $this->generateFrontendUrl( $objPage->row(), ( $strAlias ? '/' . $strAlias : '' ) );
    }
}