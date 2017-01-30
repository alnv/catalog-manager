<?php

namespace CatalogManager;

class CatalogView extends CatalogController {

    private $strTable = '';
    private $arrCatalog = [];
    private $arrCatalogFields = [];

    public function __construct() {

        parent::__construct();

        $this->import( 'SQLQueryBuilder' );
        $this->import( 'SQLHelperQueries' );
    }

    public function getCatalogByTablename( $strTablename ) {

        return $this->SQLHelperQueries->getCatalogByTablename( $strTablename );
    }

    public function getCatalogFieldsByCatalogID( $strID ) {

        return $this->SQLHelperQueries->getCatalogFieldsByCatalogID( $strID );
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

            $arrQuery['joins'] = $this->prepareFieldsJoinData( $arrView['joins'] );
        }

        if ( $arrView['joinPTable'] && $this->arrCatalog['pTable'] ) {

            $arrQuery['joins'][] = $this->preparePTableJoinData();
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

    private function preparePTableJoinData () {

        return [

            'field' => 'pid',
            'onField' => 'id',
            'multiple' => false,
            'table' => $this->strTable,
            'onTable' => $this->arrCatalog['pTable']
        ];
    }

    private function prepareFieldsJoinData ( $arrJoins ) {

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