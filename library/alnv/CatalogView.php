<?php

namespace CatalogManager;

class CatalogView extends CatalogController {

    public $strMode;
    public $strMasterID;
    public $arrViewPage;
    public $strTemplate;
    public $objMainTemplate;
    public $arrOptions = [];
    public $arrMasterPage = [];

    private $arrCatalog = [];
    private $arrCatalogFields = [];
    private $blnGoogleMapScript = false;
    private $arrCatalogStaticFields = [];
    private $arrCatalogFieldnameAndIDMap = [];

    public function __construct() {

        parent::__construct();

        $this->import( 'TemplateHelper' );
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

                if ( !$arrField['fieldname'] || !$arrField['type'] ) continue;

                if ( in_array( $arrField['type'], [ 'map' ] ) ) {

                    $this->arrCatalogStaticFields[] = $strID;

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
        $this->arrCatalog['cTables'] = Toolkit::deserialize( $this->arrCatalog['cTables'] );
        $this->arrCatalog['operations'] = Toolkit::deserialize( $this->arrCatalog['operations'] );
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
        $strPageID = 'page_e' . $this->id;
        $intOffset = $this->catalogOffset;
        $intPerPage = $this->catalogPerPage;
        $intPagination = \Input::get( $strPageID );
        $arrQuery['table'] = $this->catalogTablename;
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

        $intTotal = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( sprintf( 'SELECT COUNT(*) FROM %s', $this->catalogTablename ) )->execute()->row()['COUNT(*)'];

        if ( $this->catalogOffset ) {

            $intTotal -= $intOffset;
        }

        if ( \Input::get( $strPageID ) && $this->catalogAddPagination ) {

            $intOffset = $intPagination;

            if ( $intPerPage > 0 && $this->catalogOffset ) {

                $intOffset += round( $this->catalogOffset / $intPerPage );
            }

            $arrQuery['pagination']['offset'] = ( $intOffset - 1 ) * $intPerPage;
        }

        if ( is_array( $this->arrCatalog['operations'] ) && in_array( 'invisible', $this->arrCatalog['operations']  ) ) {

            $dteTime = \Date::floorToMinute();

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
                    'value' => ( $dteTime + 60 )
                ]
            ];

            $arrQuery['where'][] = [

                'field' => 'invisible',
                'operator' => 'not',
                'value' => '1'
            ];
        }

        $objQueryBuilderResults = $this->SQLQueryBuilder->execute( $arrQuery );

        while ( $objQueryBuilderResults->next() ) {

            $arrCatalog = $objQueryBuilderResults->row();

            if ( $this->strMode === 'master' ) {

                $this->strMasterID = $arrCatalog['id'];
            }

            $arrCatalog['contentElements'] = '';

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

            if ( $this->strMode === 'master' && $this->arrCatalog['addContentElements'] ) {

                $objContent = \ContentModel::findPublishedByPidAndTable( $arrCatalog['id'] , $this->catalogTablename );

                if ( $objContent !== null ) {

                    while ( $objContent->next() ) {

                        $arrCatalog['contentElements'] .= $this->getContentElement( $objContent->current() );
                    }
                }
            }

            if ( !empty( $this->arrCatalogStaticFields ) && is_array( $this->arrCatalogStaticFields ) ) {

                foreach ( $this->arrCatalogStaticFields as $strID ) {

                    $arrField = $this->arrCatalogFields[$strID];

                    switch ( $arrField['type'] ) {

                        case 'map':

                            if ( !$this->blnGoogleMapScript ) {

                                $this->blnGoogleMapScript = true;
                            }

                            $arrCatalog[ $arrField['fieldname'] ] = Map::parseValue( '', $arrField, $arrCatalog );

                            break;
                    }
                }
            }

            $objTemplate->setData( $arrCatalog );

            $strCatalogView .= $objTemplate->parse();
        }

        if ( $intPerPage > 0 && $this->catalogAddPagination ) {

            $this->objMainTemplate->pagination = $this->TemplateHelper->addPagination( $intTotal, $intPerPage, $strPageID, $this->arrViewPage['id'] );
        }

        if ( $this->blnGoogleMapScript ) {

            $GLOBALS['TL_HEAD']['CatalogManagerGoogleMaps'] = Map::generateGoogleMapJSInitializer();
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

    public function getCommentForm() {

        if ( !in_array( 'comments', \ModuleLoader::getActive() ) ) {

            return null;
        }

        $this->TemplateHelper->addComments(

            $this->objMainTemplate,
            
            [
                'template' => $this->com_template,
                'bbcode' => $this->catalogCommentBBCode,
                'perPage' => $this->catalogCommentPerPage,
                'order' => $this->catalogCommentSortOrder,
                'moderate' => $this->catalogCommentModerate,
                'requireLogin' => $this->catalogCommentRequireLogin,
                'disableCaptcha' => $this->catalogCommentDisableCaptcha
            ],

            $this->catalogTablename,

            '0',

            []
        );
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