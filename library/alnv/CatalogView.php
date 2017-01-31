<?php

namespace CatalogManager;

class CatalogView extends CatalogController {

    private $strTable = '';
    private $arrCatalog = [];
    private $arrCatalogFields = [];

    public $strTemplate;
    public $arrOptions = [];

    public function __construct() {

        parent::__construct();

        $this->import( 'SQLQueryHelper' );
        $this->import( 'SQLQueryBuilder' );
    }

    public function getCatalogByTablename( $strTablename ) {

        return $this->SQLQueryHelper->getCatalogByTablename( $strTablename );
    }

    public function getCatalogFieldsByCatalogID( $strID ) {

        return $this->SQLQueryHelper->getCatalogFieldsByCatalogID( $strID );
    }

    public function getCatalogViewByTable( $strTable, $arrQuery = [] ) {

        global $objPage;

        $objTemplate = null;
        $objViewPage = null;
        $strCatalogView = '';

        $this->strTable = $strTable;

        if ( !$this->SQLQueryBuilder->tableExist( $this->strTable ) ) return [];

        if ( !$arrQuery['table'] ) $arrQuery['table'] = $this->strTable;

        $this->setOptions();
        $objMasterPage = $objPage->row();

        $this->arrCatalog = $this->getCatalogByTablename( $this->strTable );
        $this->catalogItemOperations = deserialize( $this->catalogItemOperations );
        $this->arrCatalogFields = $this->getCatalogFieldsByCatalogID( $this->arrCatalog['id'] );

        if ( $this->catalogJoinFields ) {

            $this->catalogJoinFields = $this->prepareFieldsJoinData( $this->catalogJoinFields );
        }

        if ( $this->catalogJoinParentTable && $this->arrCatalog['pTable'] ) {

            $arrQuery['joins'][] = $this->preparePTableJoinData();
        }

        $objTemplate = new \FrontendTemplate( $this->strTemplate );
        $objQueryBuilderResults = $this->SQLQueryBuilder->execute( $arrQuery );

        if ( $this->catalogUseMasterPage && $this->catalogMasterPage !== '0' ) {

            $objMasterPage = $this->getPageModel( $this->catalogMasterPage );
        }

        if ( $this->catalogUseViewPage && $this->catalogViewPage !== '0' ) {

            $objViewPage = $this->getPageModel( $this->catalogViewPage );
        }

        while ( $objQueryBuilderResults->next() ) {

            $arrCatalog = $objQueryBuilderResults->row();

            $arrCatalog['link2View'] = $this->generateUrl( $objViewPage, '' );
            $arrCatalog['link2Master'] = $this->generateUrl( $objMasterPage, $arrCatalog['alias'] );

            if ( !empty( $this->catalogItemOperations ) ) {

                $arrCatalog['operationsLinks'] = $this->generateOperationsLinks( $objViewPage, $arrCatalog['id'] );
            }

            $objTemplate->setData( $arrCatalog );

            $strCatalogView .= $objTemplate->parse();
        }

        return $strCatalogView;
    }

    private function setOptions() {

        if ( !empty( $this->arrOptions ) && is_array( $this->arrOptions ) ) {

            foreach ( $this->arrOptions as $strKey => $varValue ) {

                $this->{$strKey} = $varValue;
            }
        }
    }

    private function generateOperationsLinks( $objViewPage, $strID ) {

        $arrReturn = [];

        if ( is_array( $this->catalogItemOperations ) ) {

            foreach ( $this->catalogItemOperations as $strOperation ) {

                if ( !$strOperation || $strOperation == 'create' ) continue;

                $strActFragment = sprintf( '?act%s=%s&id=%s', $this->id, $strOperation, $strID );

                $arrReturn[ $strOperation ] = $this->generateUrl( $objViewPage, '' ) . $strActFragment;
            }
        }

        return $arrReturn;
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