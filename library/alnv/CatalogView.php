<?php

namespace CatalogManager;

class CatalogView extends CatalogController {

    public $arrViewPage;
    public $strTemplate;
    public $arrOptions = [];
    public $arrMasterPage = [];
    
    private $arrCatalog = [];
    private $arrCatalogFields = [];
    private $arrCatalogFieldnameAndIDMap = [];

    public function __construct() {

        parent::__construct();

        $this->import( 'SQLQueryHelper' );
        $this->import( 'SQLQueryBuilder' );
    }

    public function initialize() {

        global $objPage;

        $this->setOptions();

        if ( !$this->catalogTablename ) return null;

        $this->arrCatalog = $this->SQLQueryHelper->getCatalogByTablename( $this->catalogTablename );

        $this->arrCatalogFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogID( $this->arrCatalog['id'] );

        if ( !empty( $this->arrCatalogFields ) && is_array( $this->arrCatalogFields ) ) {

            foreach ( $this->arrCatalogFields as $strID => $arrField ) {

                if ( !$arrField['fieldname'] ) {

                    continue;
                }

                $this->arrCatalogFieldnameAndIDMap[ $arrField['fieldname'] ] = $strID;
            }
        }

        $this->arrMasterPage = $objPage->row();

        if ( $this->catalogUseViewPage && $this->catalogViewPage !== '0' ) {

            $this->arrViewPage = $this->getPageModel( $this->catalogViewPage );
        }

        $this->catalogItemOperations = Toolkit::deserialize( $this->catalogItemOperations );
    }

    public function checkPermission() {

        $this->import( 'FrontendEditingPermission' );

        $this->FrontendEditingPermission->blnDisablePermissions = $this->catalogEnableFrontendPermission ? false : true;
        $this->FrontendEditingPermission->initialize();
        
        return $this->FrontendEditingPermission->hasAccess( $this->catalogTablename );
    }

    public function getCreateOperation() {

        $strPTableFragment = '';

        if ( !$this->FrontendEditingPermission->hasPermission( 'create', $this->catalogTablename ) ) {

            return '';
        }

        if ( empty( $this->catalogItemOperations ) || !in_array( 'create', $this->catalogItemOperations ) ) {

            return '';
        }

        if ( $this->arrCatalog['pTable'] && ( !\Input::get('pTable') || !\Input::get('pid' ) ) ) {

            return '';
        }

        if ( $this->arrCatalog['pTable'] ) {

            $strPTableFragment = sprintf( '&pTable=%s&pid=%s', \Input::get('pTable'), \Input::get('pid' ) );
        }

        return $this->generateUrl( $this->arrViewPage, '' ) . sprintf( '?act%s=create%s', $this->id, $strPTableFragment );
    }

    public function getCatalogView( $arrQuery ) {

        $strCatalogView = '';
        $objTemplate = new \FrontendTemplate( $this->strTemplate );

        if ( !$this->catalogTablename || !$this->SQLQueryBuilder->tableExist( $this->catalogTablename ) ) {

            return $strCatalogView;
        }

        if ( $this->catalogUseMasterPage && $this->catalogMasterPage !== '0' ) {

            $this->arrMasterPage = $this->getPageModel( $this->catalogMasterPage );
        }

        if ( $this->catalogJoinFields ) {

            $arrQuery['joins'] = $this->prepareJoinData( $this->catalogJoinFields );
        }

        if ( $this->catalogJoinParentTable && $this->arrCatalog['pTable'] ) {

            $arrQuery['joins'][] = $this->preparePTableJoinData();
        }

        /*
        elseif ( is_array( $this->catalogItemOperations ) && in_array( 'create', $this->catalogItemOperations ) ) {

            $arrQuery['joins'][] = $this->preparePTableJoinData();
        }
        */

        $arrQuery['table'] = $this->catalogTablename;

        $objQueryBuilderResults = $this->SQLQueryBuilder->execute( $arrQuery );

        while ( $objQueryBuilderResults->next() ) {

            $arrCatalog = $objQueryBuilderResults->row();

            if ( !empty( $arrCatalog ) && is_array( $arrCatalog ) ) {

                foreach ( $arrCatalog as $strFieldname => $varValue ) {

                    $arrCatalog[$strFieldname] = $this->parseCatalogValues( $varValue, $strFieldname, $arrCatalog );
                }
            }

            if ( !empty( $this->arrViewPage ) ) {

                $arrCatalog['link2View'] = $this->generateUrl( $this->arrViewPage, '' );
            }

            if ( !empty( $this->arrMasterPage ) ) {

                $arrCatalog['link2Master'] = $this->generateUrl( $this->arrMasterPage, $arrCatalog['alias'] );
            }

            if ( !empty( $this->catalogItemOperations ) ) {

                $arrCatalog['operationsLinks'] = $this->generateOperationsLinks( $arrCatalog['id'] );
            }

            $objTemplate->setData( $arrCatalog );

            $strCatalogView .= $objTemplate->parse();
        }

        return $strCatalogView;
    }

    public function parseCatalogValues( $varValue, $strFieldname, $arrCatalog ) {

        $strFieldID = $this->arrCatalogFieldnameAndIDMap[$strFieldname];
        $arrField = $this->arrCatalogFields[$strFieldID];
        
        switch ( $arrField['type'] ) {

            case 'upload':

                return Upload::parseValue( $varValue, $arrField, $arrCatalog );

                break;
        }

        return $varValue;
    }

    private function setOptions() {

        if ( !empty( $this->arrOptions ) && is_array( $this->arrOptions ) ) {

            foreach ( $this->arrOptions as $strKey => $varValue ) {

                $this->{$strKey} = $varValue;
            }
        }
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

    private function prepareJoinData ( $arrJoins ) {

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
            $arrRelatedJoinData['table'] = $this->catalogTablename;
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

    private function generateOperationsLinks( $strID ) {

        $arrReturn = [];

        if ( is_array( $this->catalogItemOperations ) ) {

            foreach ( $this->catalogItemOperations as $strOperation ) {

                if ( !$strOperation || $strOperation == 'create' ) continue;

                if ( !$this->FrontendEditingPermission->hasPermission( ( $strOperation === 'copy' ? 'create' : $strOperation  ), $this->catalogTablename ) ) {

                    continue;
                }

                $strActFragment = sprintf( '?act%s=%s&id=%s', $this->id, $strOperation, $strID );

                $arrReturn[] = [

                    'label' => $strOperation,
                    'href' => $this->generateUrl( $this->arrViewPage, '' ) . $strActFragment,
                    'attributes' => $strOperation === 'delete' ? 'onclick="if(!confirm(\'' . sprintf( $GLOBALS['TL_LANG']['MSC']['deleteConfirm'], $strID ) . '\'))return false;"' : ''
                ];
            }
        }

        return $arrReturn;
    }

    private function preparePTableJoinData () {

        return [

            'field' => 'pid',
            'onField' => 'id',
            'multiple' => false,
            'table' => $this->catalogTablename,
            'onTable' => $this->arrCatalog['pTable']
        ];
    }
}