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

    protected $arrCatalog = [];
    protected $arrActiveFields = [];
    protected $arrCatalogFields = [];
    protected $blnMapViewMode = false;
    protected $blnGoogleMapScript = false;
    protected $arrCatalogStaticFields = [];
    protected $arrCatalogMapViewOptions = [];


    public function __construct() {

        parent::__construct();

        $this->import( 'TemplateHelper' );
        $this->import( 'SQLQueryHelper' );
        $this->import( 'SQLQueryBuilder' );
        $this->import( 'DCABuilderHelper' );
        $this->import( 'I18nCatalogTranslator' );
        $this->import( 'FrontendEditingPermission' );
    }


    public function initialize() {

        global $objPage;
        
        $this->setOptions();
        $arrPage = $objPage->row();

        $this->I18nCatalogTranslator->initialize();

        if ( !$this->catalogTablename ) return null;

        $this->arrCatalogFields = $this->DCABuilderHelper->getPredefinedFields();
        $this->arrCatalog = $this->SQLQueryHelper->getCatalogByTablename( $this->catalogTablename );
        $this->arrCatalogFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogID( $this->arrCatalog['id'], null, $this->arrCatalogFields );

        if ( !empty( $this->arrCatalogFields ) && is_array( $this->arrCatalogFields ) ) {

            foreach ( $this->arrCatalogFields as $strID => $arrField ) {

                if ( !$arrField['fieldname'] || !$arrField['type'] ) continue;

                $arrFieldLabels = $this->I18nCatalogTranslator->getFieldLabel( $arrField['fieldname'], $arrField['title'], $arrField['description'] );

                $this->arrCatalogFields[ $strID ][ 'title' ] = $arrFieldLabels[0];
                $this->arrCatalogFields[ $strID ][ 'description' ] = $arrFieldLabels[1];

                if ( !$arrField['invisible'] && is_numeric( $strID ) ) {
                    
                    $this->arrActiveFields[] = $arrField['fieldname'];
                }

                if ( in_array( $arrField['type'], [ 'map', 'message' ] ) ) {

                    $this->arrCatalogStaticFields[] = $strID;
                }
            }
        }

        $this->rebuildCatalogFieldIndexes();

        $this->arrMasterPage = $arrPage;
        $this->arrViewPage = $arrPage;

        if ( $this->catalogUseViewPage && $this->catalogViewPage !== '0' ) {

            $this->arrViewPage = $this->getPageModel( $this->catalogViewPage );
        }

        if ( $this->catalogUseMap && $this->strMode == 'view' ) {
            
            $this->arrCatalogMapViewOptions = Map::getMapViewOptions([

                'id' => 'map_' . $this->id,
                'lat' => $this->catalogMapLat,
                'lng' => $this->catalogMapLng,
                'mapZoom' => $this->catalogMapZoom,
                'mapType' => $this->catalogMapType,
                'mapStyle' => $this->catalogMapStyle,
                'mapMarker' => $this->catalogMapMarker,
                'addMapInfoBox' => $this->catalogAddMapInfoBox,
                'mapScrollWheel' => $this->catalogMapScrollWheel
            ]);

            $this->blnMapViewMode = true;
            $this->blnGoogleMapScript = true;
            $this->strTemplate = $this->catalogMapTemplate;
        }

        $this->catalogOrderBy = Toolkit::deserialize( $this->catalogOrderBy );
        $this->catalogTaxonomies = Toolkit::deserialize( $this->catalogTaxonomies );
        $this->catalogJoinFields = Toolkit::parseStringToArray( $this->catalogJoinFields );
        $this->catalogItemOperations = Toolkit::deserialize( $this->catalogItemOperations );
        $this->arrCatalog['cTables'] = Toolkit::deserialize( $this->arrCatalog['cTables'] );
        $this->arrCatalog['operations'] = Toolkit::deserialize( $this->arrCatalog['operations'] );
        $this->catalogRelatedChildTables = Toolkit::deserialize( $this->catalogRelatedChildTables );
    }


    public function checkPermission() {

        $this->FrontendEditingPermission->blnDisablePermissions = $this->catalogEnableFrontendPermission ? false : true;
        $this->FrontendEditingPermission->initialize();
        
        return $this->FrontendEditingPermission->hasAccess( $this->catalogTablename );
    }


    public function getActiveCatalogFields() {

        return $this->arrActiveFields;
    }


    protected function rebuildCatalogFieldIndexes() {

        $arrReturn = [];

        if ( !empty( $this->arrCatalogFields ) && is_array( $this->arrCatalogFields ) ) {

            foreach ( $this->arrCatalogFields as $arrCatalogField ) {

                if ( !$arrCatalogField['fieldname'] ) continue;

                $arrReturn[ $arrCatalogField['fieldname'] ] = $arrCatalogField;
             }
        }

        $this->arrCatalogFields = $arrReturn;
    }


    public function getCreateOperation() {

        $strPTableFragment = '';
        $this->loadLanguageFile( 'tl_module' );

        if ( !$this->FrontendEditingPermission->hasPermission( 'create', $this->catalogTablename ) ) {

            return [];
        }

        if ( empty( $this->catalogItemOperations ) || !in_array( 'create', $this->catalogItemOperations ) ) {

            return [];
        }

        if ( $this->arrCatalog['pTable'] && ( !\Input::get('pTable') || !\Input::get('pid' ) ) ) {

            return [];
        }

        if ( $this->arrCatalog['pTable'] ) {

            $strPTableFragment = sprintf( '&pTable=%s&pid=%s', \Input::get('pTable'), \Input::get('pid' ) );
        }

        return [

            'href' => $this->generateUrl( $this->arrViewPage, '' ) . sprintf( '?act%s=create%s', $this->id, $strPTableFragment ),
            'label' => $GLOBALS['TL_LANG']['tl_module']['reference']['catalogItemOperations']['create'],
            'attributes' => ''
        ];
    }


    public function getMapViewOptions() {

        return $this->arrCatalogMapViewOptions;
    }


    public function getCatalogView( $arrQuery ) {

        $arrCatalogItems = [];
        $strPageID = 'page_e' . $this->id;
        $intOffset = $this->catalogOffset;
        $intPerPage = $this->catalogPerPage;
        $intPagination = \Input::get( $strPageID );
        $arrQuery['table'] = $this->catalogTablename;
        $objTemplate = new \FrontendTemplate( $this->strTemplate );

        if ( !$this->catalogTablename || !$this->SQLQueryBuilder->tableExist( $this->catalogTablename ) ) {

            return '';
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

        if ( $this->strMode == 'view' && !empty( $this->catalogTaxonomies['query'] ) && is_array( $this->catalogTaxonomies['query'] ) && $this->catalogUseTaxonomies ) {

            $arrQuery['where'] = Toolkit::parseWhereQueryArray( $this->catalogTaxonomies['query'], function ( $arrQuery ) {

                $arrField = $this->arrCatalogFields[ $arrQuery['field'] ];
                $arrQuery['value'] = $this->getParseQueryValue( $arrField, $arrQuery['value'], $arrQuery['operator'] );

                if ( is_null( $arrQuery['value'] ) || $arrQuery['value'] === '' ) {

                    return null;
                }

                if ( empty( $arrQuery['value'] ) && is_array( $arrQuery['value'] ) ) {

                    return null;
                }

                if ( is_array( $arrQuery['value'] ) && $arrQuery['operator'] != 'contain' ) {

                    $arrQuery['multiple'] = true;
                }

                return $arrQuery;
            });
        }

        if ( is_array( $this->arrCatalog['operations'] ) && in_array( 'invisible', $this->arrCatalog['operations'] ) && !BE_USER_LOGGED_IN ) {

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
                    'value' => $dteTime
                ]
            ];

            $arrQuery['where'][] = [

                'field' => 'invisible',
                'operator' => 'not',
                'value' => '1'
            ];
        }
        
        if ( $this->catalogUseRadiusSearch && $this->strMode == 'view' ) {

            $arrRSValues = [];
            $arrRSAttributes = [ 'rs_cty', 'rs_strt', 'rs_pstl', 'rs_cntry', 'rs_strtn' ];

            foreach ( $arrRSAttributes as $strSRAttribute ) {

                if ( \Input::get( $strSRAttribute ) && is_string( \Input::get( $strSRAttribute ) ) ) {

                    $arrRSValues[ $strSRAttribute ] = \Input::get( $strSRAttribute );
                }
            }

            if ( !empty( $arrRSValues ) && is_array( $arrRSValues ) ) {

                if ( !$arrRSValues['rs_cntry'] && $this->catalogRadioSearchCountry ) {

                    $arrRSValues['rs_cntry'] = $this->catalogRadioSearchCountry;
                }

                $objGeoCoding = new GeoCoding();
                $objGeoCoding->setCity( $arrRSValues['rs_cty'] );
                $objGeoCoding->setStreet( $arrRSValues['rs_strt'] );
                $objGeoCoding->setPostal( $arrRSValues['rs_pstl'] );
                $objGeoCoding->setCountry( $arrRSValues['rs_cntry'] );
                $objGeoCoding->setStreetNumber( $arrRSValues['rs_strtn'] );

                $arrCords = $objGeoCoding->getCords( '', 'en', true );

                $arrQuery['distance'] = [

                    'latCord' => $arrCords['lat'],
                    'lngCord' => $arrCords['lng'],
                    'latField' => $this->catalogFieldLat,
                    'lngField' => $this->catalogFieldLng,
                    'value' => \Input::get( 'rs_dstnc' ) ? \Input::get( 'rs_dstnc' ) : '50'
                ];
            }
        }

        if ( !empty( $this->catalogOrderBy )  && is_array( $this->catalogOrderBy ) ) {

            foreach ( $this->catalogOrderBy as $arrOrderBy ) {

                if ( $arrOrderBy['key'] && $arrOrderBy['value'] ) {

                    $arrQuery['orderBy'][] = [

                        'field' => $arrOrderBy['key'],
                        'order' => $arrOrderBy['value']
                    ];
                }
            }
        }

        if ( $this->catalogEnableParentFilter ) {

            if ( $this->arrCatalog['pTable'] && \Input::get( 'pid' ) && \Input::get( 'pTable' ) == $this->arrCatalog['pTable'] ) {

                $arrQuery['where'][] = [

                    'field' => 'pid',
                    'operator' => 'equal',
                    'value' => \Input::get( 'pid' )
                ];
            }
        }

        $strWhereStatement = $this->SQLQueryBuilder->getWhereQuery( $arrQuery );
        $intTotal = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( sprintf( 'SELECT COUNT(*) FROM %s%s', $this->catalogTablename, $strWhereStatement ) )->execute( $this->SQLQueryBuilder->getValues() )->row()[ 'COUNT(*)' ];

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

        $intIndex = 0;
        $objQueryBuilderResults = $this->SQLQueryBuilder->execute( $arrQuery );
        $intResultRows = $objQueryBuilderResults->numRows;

        while ( $objQueryBuilderResults->next() ) {

            $arrCatalog = $objQueryBuilderResults->row();
            $arrCatalog['cssClass'] = $intIndex % 2 ? ' even' : ' odd';
            $arrCatalog['entityIndex'] = [ $intIndex + 1, $intResultRows ];

            if ( !$intIndex ) {

                $arrCatalog['cssClass'] .= ' first';
            }

            if ( $intIndex == ( $intResultRows - 1 ) ) {

                $arrCatalog['cssClass'] .= ' last';
            }

            if ( $this->strMode === 'master' ) {

                $this->strMasterID = $arrCatalog['id'];
            }

            if ( !empty( $arrCatalog ) && is_array( $arrCatalog ) ) {

                foreach ( $arrCatalog as $strFieldname => $varValue ) {

                    $arrCatalog[$strFieldname] = $this->parseCatalogValues( $varValue, $strFieldname, $arrCatalog );
                }
            }

            if ( !empty( $this->arrViewPage ) ) {

                $arrCatalog['goBackLink'] = $this->generateUrl( $this->arrViewPage, '' );
                $arrCatalog['goBackLabel'] = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['back'];
            }

            $arrCatalog['masterUrl'] = $this->getMasterRedirect( $arrCatalog, $arrCatalog['alias'] );

            if ( !empty( $this->catalogItemOperations ) ) {

                $arrCatalog['operations'] = $this->generateOperations( $arrCatalog['id'], $arrCatalog['alias'] );
            }

            if ( $this->catalogUseRelation ) {

                $arrCatalog['relations'] = $this->setRelatedTableLinks( $arrCatalog['id'] );
            }

            $arrCatalog['contentElements'] = '';

            if ( $this->strMode === 'master' && $this->arrCatalog['addContentElements'] ) {

                $objContent = \ContentModel::findPublishedByPidAndTable( $arrCatalog['id'] , $this->catalogTablename );

                if ( $objContent !== null ) {

                    while ( $objContent->next() ) {

                        $arrCatalog['contentElements'] .= $this->getContentElement( $objContent->current() );
                    }
                }
            }

            if ( !empty( $this->arrCatalogStaticFields ) && is_array( $this->arrCatalogStaticFields ) && !$this->blnMapViewMode ) {

                foreach ( $this->arrCatalogStaticFields as $strID ) {

                    $arrField = $this->arrCatalogFields[ $strID ];

                    switch ( $arrField['type'] ) {

                        case 'map':

                            if ( !$this->blnGoogleMapScript ) {

                                $this->blnGoogleMapScript = true;
                            }

                            $arrCatalog[ $arrField['fieldname'] ] = Map::parseValue( '', $arrField, $arrCatalog );

                            break;

                        case 'message':

                            $arrCatalog[ $arrField['fieldname'] ] = MessageInput::parseValue( '', $arrField, $arrCatalog );

                            break;
                    }
                }
            }

            if ( $this->blnMapViewMode ) {

                $this->arrCatalogMapViewOptions['mapInfoBoxContent'] = Map::parseInfoBoxContent( $this->catalogMapInfoBoxContent, $arrCatalog );
                $this->arrCatalogMapViewOptions['locationLat'] = $arrCatalog[$this->catalogFieldLat];
                $this->arrCatalogMapViewOptions['locationLng'] = $arrCatalog[$this->catalogFieldLng];

                $arrCatalog['map'] = $this->arrCatalogMapViewOptions;
            }

            if ( $this->strMode == 'master' ) {

                global $objPage;

                if ( $this->catalogSEOTitle ) {

                    $objPage->pageTitle = $arrCatalog[ $this->catalogSEOTitle ] ? strip_tags( $arrCatalog[ $this->catalogSEOTitle ] ) : '';
                }

                if ( $this->catalogSEODescription ) {

                    $objPage->description = $arrCatalog[ $this->catalogSEODescription ] ? strip_tags( $arrCatalog[ $this->catalogSEODescription ] ) : '';
                }
            }

            $arrCatalog['catalogFields'] = $this->arrCatalogFields;
            $arrCatalog['activeFields'] = $this->getActiveCatalogFields();

            $objTemplate->setData( $arrCatalog );

            $arrCatalogItems[] = $objTemplate->parse();
            $intIndex++;
        }
        
        if ( $intPerPage > 0 && $this->catalogAddPagination && $this->strMode == 'view' ) {

            $this->objMainTemplate->pagination = $this->TemplateHelper->addPagination( $intTotal, $intPerPage, $strPageID, $this->arrViewPage['id'] );
        }

        if ( $this->blnGoogleMapScript ) {

            $GLOBALS['TL_HEAD']['CatalogManagerGoogleMaps'] = Map::generateGoogleMapJSInitializer();
        }

        if ( $this->catalogTemplateDebug ) {

            $objDebugTemplate = new \FrontendTemplate( 'ctlg_debug_default' );
            $GLOBALS['TL_CSS']['catalogManagerFrontendExtension'] = $GLOBALS['TL_CONFIG']['debugMode']
                ? 'system/modules/catalog-manager/assets/debug.css'
                : 'system/modules/catalog-manager/assets/debug.css';

            $objDebugTemplate->setData([

                'catalogTemplate' => $this->strTemplate,
                'catalogFields' => $this->arrCatalogFields,
                'activeFields' => $this->getActiveCatalogFields(),
                'activeFieldsHeadline' => $this->getActiveFieldsHeadline( $this->strTemplate ),
                'activeFieldsOutput' => $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['activeFieldsOutput'],

                'open_html' => htmlentities('<'),
                'close_tag' => htmlentities('>'),
                'close_php' => htmlentities('?>'),
                'echo_php' => htmlentities('<?='),
                'close_html' => htmlentities('</'),
                'open_php' => htmlentities('<?php'),
            ]);

            $this->objMainTemplate->debug = $objDebugTemplate->parse();
        }

        if ( $this->catalogRandomSorting ) {

            shuffle( $arrCatalogItems );
        }

        return implode( '', $arrCatalogItems );
    }


    protected function getActiveFieldsHeadline( $strTemplate ) {

        return sprintf( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['activeFieldsHeadline'], $strTemplate );
    }


    protected function setRelatedTableLinks( $strID ) {

        $arrReturn = [];

        if ( is_array( $this->catalogRelatedChildTables ) ) {

            foreach ( $this->catalogRelatedChildTables as $arrRelatedChild ) {

                if ( !$arrRelatedChild['table'] ) continue;

                $strUrl = \Controller::replaceInsertTags( $arrRelatedChild['pageURL'] );
                $strSuffix = sprintf( '?pid=%s&amp;pTable=%s', $strID, $this->catalogTablename );

                $arrReturn[ $arrRelatedChild['table'] ] = [

                    'href' => $strUrl . $strSuffix,
                    'label' => $arrRelatedChild['table'],
                    'attributes' => ''
                ];
            }
        }

        return $arrReturn;
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

        return $this->generateUrl( $this->arrMasterPage, $strAlias );
    }


    public function parseCatalogValues( $varValue, $strFieldname, $arrCatalog ) {

        $arrField = $this->arrCatalogFields[ $strFieldname ];

        switch ( $arrField['type'] ) {

            case 'upload':

                return Upload::parseValue( $varValue, $arrField, $arrCatalog );

                break;

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


    public function getCommentForm() {

        if ( !in_array( 'comments', \ModuleLoader::getActive() ) ) {

            return null;
        }

        if ( !$this->catalogAllowComments ) {

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


    protected function getParseQueryValue( $arrField, $strValue = '', $strOperator = '' ) {

        $varValue = \Input::get( $arrField['fieldname'] . $this->id ) ? \Input::get( $arrField['fieldname'] . $this->id ) : $strValue;
        $varValue = \Controller::replaceInsertTags( $varValue );

        if ( $varValue && ( $arrField['type'] == 'checkbox' || $arrField['multiple'] || $strOperator == 'contain' ) ) {

            $varValue = is_string( $varValue ) ? explode( ',', $varValue ) : $varValue;
        }

        return Toolkit::prepareValueForQuery( $varValue );
    }


    protected function setOptions() {

        if ( !empty( $this->arrOptions ) && is_array( $this->arrOptions ) ) {

            foreach ( $this->arrOptions as $strKey => $varValue ) {

                $this->{$strKey} = $varValue;
            }
        }
    }


    protected function getPageModel( $strID ) {

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


    protected function prepareJoinData ( $arrJoins ) {

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


    protected function generateUrl( $objPage, $strAlias ) {

        if ( $objPage == null ) return '';

        return $this->generateFrontendUrl( $objPage, ( $strAlias ? '/' . $strAlias : '' ) );
    }


    protected function generateOperations( $strID, $strAlias = '' ) {

        $arrReturn = [];
        $this->loadLanguageFile( 'tl_module' );

        if ( is_array( $this->catalogItemOperations ) ) {

            foreach ( $this->catalogItemOperations as $strOperation ) {

                if ( !$strOperation || $strOperation == 'create' ) continue;

                if ( !$this->FrontendEditingPermission->hasPermission( ( $strOperation === 'copy' ? 'create' : $strOperation  ), $this->catalogTablename ) ) {

                    continue;
                }

                $strActFragment = sprintf( '?act%s=%s&id=%s', $this->id, $strOperation, $strID );

                $arrReturn[ $strOperation ] = [

                    'href' => $this->generateUrl( $this->arrViewPage, $strAlias ) . $strActFragment,
                    'label' => $GLOBALS['TL_LANG']['tl_module']['reference']['catalogItemOperations'][ $strOperation ],
                    'attributes' => $strOperation === 'delete' ? 'onclick="if(!confirm(\'' . sprintf( $GLOBALS['TL_LANG']['MSC']['deleteConfirm'], $strID ) . '\'))return false;"' : ''
                ];
            }
        }

        return $arrReturn;
    }


    protected function preparePTableJoinData () {

        return [

            'field' => 'pid',
            'onField' => 'id',
            'multiple' => false,
            'table' => $this->catalogTablename,
            'onTable' => $this->arrCatalog['pTable']
        ];
    }
}