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

        $objTemplate = null;
        $arrCatalogData = [ 'view' => '', 'data' => [] ];

        if ( !$this->SQLQueryBuilder->tableExist( $strTable ) ) return [];

        if ( !$arrQuery['table'] ) $arrQuery['table'] = $strTable;

        $objQueryBuilderResults = $this->SQLQueryBuilder->execute( $arrQuery );

        if ( $arrView['useTemplate'] ) {

            $objTemplate = new \FrontendTemplate( $arrView['template'] );
        }

        while ( $objQueryBuilderResults->next() ) {

            $arrCatalog = $objQueryBuilderResults->row();

            $arrCatalog['link2View'] = 'catalog.html';
            $arrCatalog['link2Master'] = sprintf( 'catalog/%s', $arrCatalog['alias'] ) . '.html';

            if ( $objTemplate !== null ) {

                $objTemplate->setData( $arrCatalog );

                $arrCatalogData['view'] .= $objTemplate->parse();
            }

            $arrCatalogData['data'][ $objQueryBuilderResults->id ] = $arrCatalog;
        }

        return $arrCatalogData;
    }
}