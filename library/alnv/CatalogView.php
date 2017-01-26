<?php

namespace CatalogManager;

class CatalogView extends CatalogController {

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

            $arrFields[ $objFields->fieldname ] = $objFields->row();
        }

        return $arrFields;
    }

    public function getCatalogDataByTable( $strTable, $arrView = [], $arrQuery = [] ) {

        if ( !$this->SQLQueryBuilder->tableExist( $strTable ) ) return [];

        if ( !$arrQuery['table'] ) $arrQuery['table'] = $strTable;

        global $objPage;

        $objTemplate = null;
        $objViewPage = null;
        $objMasterPage = $objPage->row();
        $arrCatalogData = [ 'view' => '', 'data' => [] ];

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