<?php

namespace CatalogManager;

class CatalogView extends CatalogController {

    private $strTable = '';
    private $arrCatalog = [];
    private $arrCatalogFields = [];

    public function __construct() {

        parent::__construct();

        $this->import( 'SQLQueryBuilder' );
    }

    public function getCatalogByTablename( $strTablename ) {

        if ( !$strTablename ) {

            return [];
        }

        return $this->SQLQueryBuilder->execute([

            'table' => 'tl_catalog',

            'pagination' => [

                'limit' => 1,
                'offset' => 0,
            ],

            'where' => [

                [
                    'operator' => 'equal',
                    'field' => 'tablename',
                    'value' => $strTablename
                ]
            ]

        ])->row();
    }

    public function getCatalogFieldsByCatalogID( $strID ) {

        $arrFields = [];

        if ( !$strID ) {

            return $arrFields;
        }

        $objFields = $this->SQLQueryBuilder->execute([

            'table' => 'tl_catalog_fields',

            'orderBY' => [

                'order' => 'DESC',
                'field' => 'sorting'
            ],

            'where' => [

                [
                    'field' => 'pid',
                    'value' => $strID,
                    'operator' => 'equal'
                ]
            ]

        ]);

        while ( $objFields->next() ) {

            $arrFields[ $objFields->id ] = $objFields->row();
        }

        return $arrFields;
    }

    public function getCatalogDataByTable( $strTable, $arrView = [], $arrQuery = [] ) {

        $this->strTable = $strTable;

        if ( !$this->SQLQueryBuilder->tableExist( $this->strTable ) ) return [];

        if ( !$arrQuery['table'] ) $arrQuery['table'] = $this->strTable;

        global $objPage;

        $objTemplate = null;
        $objViewPage = null;
        $objMasterPage = $objPage->row();
        $arrCatalogData = [ 'view' => '', 'data' => [] ];

        $this->arrCatalog = $this->getCatalogByTablename( $this->strTable );
        $this->arrCatalogFields = $this->getCatalogFieldsByCatalogID( $this->arrCatalog['id'] );

        if ( $arrView['joins'] ) {

            $arrQuery['joins'] = $this->prepareRelationData( $arrView['joins'] );
        }

        $objQueryBuilderResults = $this->SQLQueryBuilder->execute( $arrQuery );

        if ( $arrView['useTemplate'] ) {

            $objTemplate = new \FrontendTemplate( $arrView['template'] );
        }

        if ( $arrView['useMasterPage'] && $arrView['masterPage'] !== '0' ) {

            $objMasterPage = $this->getPageModel( $arrView['masterPage'] );
        }

        if ( $arrView['useViewPage'] && $arrView['viewPage'] !== '0' ) {

            $objViewPage = $this->getPageModel( $arrView['viewPage'] );
        }

        while ( $objQueryBuilderResults->next() ) {

            $arrCatalog = $objQueryBuilderResults->row();

            $arrCatalog['link2View'] = $this->generateUrl( $objViewPage, '' );
            $arrCatalog['link2Master'] = $this->generateUrl( $objMasterPage, $arrCatalog['alias'] );

            if ( $objTemplate !== null ) {

                $objTemplate->setData( $arrCatalog );

                $arrCatalogData['view'] .= $objTemplate->parse();
            }
            
            $arrCatalogData['data'][ $objQueryBuilderResults->id ] = $arrCatalog;
        }

        return $arrCatalogData;
    }

    private function prepareRelationData ( $arrJoins ) {

        $arrReturn = [];

        if ( empty( $arrJoins ) || !is_array( $arrJoins ) ) {

            return $arrReturn;
        }

        foreach ( $arrJoins as $strFieldJoinID ) {

            $arrRelatedJoinData = [];

            if ( !$this->arrCatalogFields[ $strFieldJoinID ] ) {

                continue;
            }

            $arrRelatedJoinData['multiple'] = false;
            $arrRelatedJoinData['table'] = $this->strTable;
            $arrRelatedJoinData['field'] = $this->arrCatalogFields[ $strFieldJoinID ]['fieldname'];
            $arrRelatedJoinData['onTable'] = $this->arrCatalogFields[ $strFieldJoinID ]['dbTable'];
            $arrRelatedJoinData['onField'] = $this->arrCatalogFields[ $strFieldJoinID ]['dbTableKey'];

            if ( $this->arrCatalogFields[ $strFieldJoinID ]['multiple'] || $this->arrCatalogFields[ $strFieldJoinID ]['type'] == 'checkbox' ) {

                $arrRelatedJoinData['multiple'] = true;
            }

            $arrReturn[] = $arrRelatedJoinData;
        }

        return $arrReturn;
    }

    private function generateUrl( $objPage, $strAlias ) {

        if ( $objPage == null ) return '';

        return $this->generateFrontendUrl( $objPage, ( $strAlias ? '/' . $strAlias : '' ) );
    }

    private function getPageModel( $strID ) {

        return $this->SQLQueryBuilder->execute([

            'table' => 'tl_page',

            'pagination' => [

                'limit' => 1,
                'offset' => 0
            ],

            'where' => [

                [
                    'field' => 'id',
                    'value' => $strID,
                    'operator' => 'equal'
                ]
            ]

        ])->row();
    }
}