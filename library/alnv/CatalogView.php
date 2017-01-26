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
                    'field' => 'tablename',
                    'value' => $strTablename,
                    'operator' => 'equal'
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
                ],
            ]

        ]);

        while ( $objFields->next() ) {

            $arrFields[ $objFields->fieldname ] = $objFields->row();
        }

        return $arrFields;
    }

    public function getCatalogDataByTable( $strTable, $arrOptions = [] ) {

        if ( !$this->SQLQueryBuilder->tableExist( $strTable ) ) {

            return [];
        }

        if ( !$arrOptions['table'] ) $arrOptions['table'] = $strTable;

        $arrCatalogData = [];
        $objQueryBuilderResults = $this->SQLQueryBuilder->execute( $arrOptions );

        while ( $objQueryBuilderResults->next() ) {

            $arrCatalogData[ $objQueryBuilderResults->id ] = $objQueryBuilderResults->row();
        }
        
        return $arrCatalogData;
    }
}