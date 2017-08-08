<?php

namespace CatalogManager;

class CatalogView extends CatalogController {


    public $strMode;
    public $strMasterID;
    public $strTemplate;
    public $objMainTemplate;
    public $arrOptions = [];

    public $strTimeFormat = 'H:i';
    public $strDateFormat = 'd.m.Y';
    public $strDateTimeFormat = 'd.m.Y H:i';

    public $arrPage = [];
    public $arrViewPage = [];
    public $arrMasterPage = [];
    public $arrFrontendEditingPage = [];

    protected $arrGroups = [];
    protected $arrCatalog = [];
    protected $arrActiveFields = [];
    protected $arrEntityFields = [];
    protected $arrCatalogFields = [];
    protected $arrRelatedTables = [];
    protected $blnMapViewMode = false;
    protected $blnShowAsGroup = false;
    protected $blnHasOperations = false;
    protected $arrRoutingParameter = [];
    protected $blnGoogleMapScript = false;
    protected $arrCatalogStaticFields = [];
    protected $arrCatalogMapViewOptions = [];
    protected $arrDoNotRenderInFastMode = [ 'upload', 'map' ];


    public function __construct() {

        parent::__construct();

        $this->import( 'IconGetter' );
        $this->import( 'CatalogInput' );
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

        $this->strTimeFormat = $objPage->timeFormat;
        $this->strDateFormat = $objPage->dateFormat;
        $this->strDateTimeFormat = $objPage->datimFormat;

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

                $this->setPreviewEntityFields( $arrField['fieldname'], $arrField );
            }
        }

        $this->rebuildCatalogFieldIndexes();

        $this->arrPage = $objPage->row();
        $this->arrMasterPage = $this->arrPage;
        $this->arrFrontendEditingPage = $this->arrPage;

        if ( $this->catalogUseViewPage && $this->catalogViewPage !== '0' ) {

            $this->arrViewPage = $this->getPageModel( $this->catalogViewPage );
        }

        if ( $this->catalogUseMasterPage && $this->catalogMasterPage !== '0' ) {

            $this->arrMasterPage = $this->getPageModel( $this->catalogMasterPage );
        }

        if ( $this->catalogUseFrontendEditingViewPage && $this->catalogFrontendEditingViewPage !== '0' ) {

            $this->arrFrontendEditingPage = $this->getPageModel( $this->catalogFrontendEditingViewPage );
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

        $this->setRelatedTables();

        if ( $objPage->catalogRoutingTable && $objPage->catalogRoutingTable !== $this->catalogTablename ) {

            $objPage->catalogUseRouting = '';
        }

        if ( $objPage->catalogUseRouting && $objPage->catalogRouting ) {

            $this->arrRoutingParameter = Toolkit::getRoutingParameter( $objPage->catalogRouting );
        }

        if ( $this->enableTableView && $this->strMode == 'view' ) {

            $this->strTemplate = $this->catalogTableBodyViewTemplate;
            $this->catalogActiveTableColumns = $this->setActiveTableColumns();

            $this->objMainTemplate->activeTableColumns = $this->catalogActiveTableColumns;
            $this->objMainTemplate->hasRelations = $this->catalogUseRelation ? true : false;
            $this->objMainTemplate->readMoreColumnTitle = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['detailLink'];
            $this->objMainTemplate->relationsColumnTitle = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['relationsLinks'];
            $this->objMainTemplate->operationsColumnTitle = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['operationsLinks'];
        }

        $this->blnShowAsGroup = $this->catalogGroupBy && $this->strMode == 'view' ? true : false;

        $this->objMainTemplate->timeFormat = $this->strTimeFormat;
        $this->objMainTemplate->dateFormat = $this->strDateFormat;
        $this->objMainTemplate->catalogFields = $this->arrCatalogFields;
        $this->objMainTemplate->dateTimeFormat = $this->strDateTimeFormat;
        $this->objMainTemplate->catalogEntityFields = $this->arrEntityFields;

        $this->FrontendEditingPermission->blnDisablePermissions = $this->catalogEnableFrontendPermission ? false : true;

        if ( !$this->FrontendEditingPermission->blnDisablePermissions ) {

            $this->FrontendEditingPermission->initialize();
        }

        $this->setHasOperationsFlag();
    }


    public function showAsGroup() {

        return $this->blnShowAsGroup;
    }


    public function getHasOperationFlag() {

        return $this->blnHasOperations;
    }


    public function setActiveTableColumns() {

        $this->catalogActiveTableColumns = Toolkit::deserialize( $this->catalogActiveTableColumns );

        if ( !is_array( $this->catalogActiveTableColumns ) ) {

            $this->catalogActiveTableColumns = [];
        }

        if ( empty( $this->catalogActiveTableColumns ) ) {

            $this->catalogActiveTableColumns = array_keys( $this->arrCatalogFields );
        }

        foreach ( $this->catalogActiveTableColumns as $strIndex => $strActiveTableColumn ) {

            $arrField = $this->arrCatalogFields[ $strActiveTableColumn ];

            if ( is_array( $arrField ) ) {

                if ( $arrField['type'] == 'upload' && $arrField['useArrayFormat'] ) {

                    unset( $this->catalogActiveTableColumns[ $strIndex ] );
                }
            }
        }

        return $this->catalogActiveTableColumns;
    }


    public function getActiveCatalogFields() {

        return $this->arrActiveFields;
    }


    protected function setPreviewEntityFields( $strFieldname, $arrField ) {

        if ( in_array( $strFieldname, [ 'title', 'alias', 'id', 'pid', 'sorting', 'tstamp' ] ) ) {

            return null;
        }

        if ( $arrField['type'] == 'dbColumn' ) {

            return null;
        }

        if ( $arrField['type'] == 'upload' && $arrField['useArrayFormat'] ) {

            return null;
        }

        $this->arrEntityFields[ $strFieldname ] = $arrField;
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


    public function getMapViewOptions() {

        return $this->arrCatalogMapViewOptions;
    }


    public function getCatalogView( $arrQuery ) {

        $strPageID = 'page_e' . $this->id;
        $intOffset = $this->catalogOffset;
        $intPerPage = $this->catalogPerPage;
        $intPagination = \Input::get( $strPageID );
        $arrQuery['table'] = $this->catalogTablename;

        if ( !$this->catalogTablename || !$this->SQLQueryBuilder->tableExist( $this->catalogTablename ) ) {

            return '';
        }

        if ( !empty( $this->catalogJoinFields ) || is_array( $this->catalogJoinFields ) ) {

            $arrQuery['joins'] = $this->prepareJoinData();
        }

        if ( $this->catalogJoinParentTable && $this->arrCatalog['pTable'] ) {

            $arrQuery['joins'][] = $this->preparePTableJoinData();
        }

        if ( $this->strMode == 'view' && !empty( $this->catalogTaxonomies['query'] ) && is_array( $this->catalogTaxonomies['query'] ) && $this->catalogUseTaxonomies ) {

            $arrQuery['where'] = Toolkit::parseQueries( $this->catalogTaxonomies['query'] );
        }

        if ( is_array( $this->arrCatalog['operations'] ) && in_array( 'invisible', $this->arrCatalog['operations'] ) && !BE_USER_LOGGED_IN ) {

            $dteTime = \Date::floorToMinute();

            $arrQuery['where'][] = [

                'field' => 'tstamp',
                'operator' => 'gt',
                'value' => '0'
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
        
        if ( $this->catalogUseRadiusSearch && $this->strMode == 'view' ) {

            $arrRSValues = [];
            $arrRSAttributes = [ 'rs_cty', 'rs_strt', 'rs_pstl', 'rs_cntry', 'rs_strtn' ];

            foreach ( $arrRSAttributes as $strSRAttribute ) {

                $strValue = $this->CatalogInput->getActiveValue( $strSRAttribute );

                if ( !Toolkit::isEmpty( $strValue ) && is_string( $strValue ) ) {

                    $arrRSValues[ $strSRAttribute ] = $strValue;
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
                $strDistance = $this->CatalogInput->getActiveValue( 'rs_dstnc' );
                $arrCords = $objGeoCoding->getCords( '', 'en', true );

                if ( Toolkit::isEmpty( $strDistance ) || is_array( $strDistance ) ) {

                    $strDistance = '50';
                }

                $arrQuery['distance'] = [

                    'value' => $strDistance,
                    'latCord' => $arrCords['lat'],
                    'lngCord' => $arrCords['lng'],
                    'latField' => $this->catalogFieldLat,
                    'lngField' => $this->catalogFieldLng
                ];
            }
        }

        if ( is_array( $this->catalogOrderBy ) ) {

            $this->setOrderByParameters();

            if ( !empty( $this->catalogOrderBy ) ) {

                foreach ( $this->catalogOrderBy as $arrOrderBy ) {

                    if ( $arrOrderBy['key'] && $arrOrderBy['value'] ) {

                        $arrQuery['orderBy'][] = [

                            'field' => $arrOrderBy['key'],
                            'order' => $arrOrderBy['value']
                        ];
                    }
                }
            }
        }

        if ( $this->catalogEnableParentFilter ) {

            if ( \Input::get( 'pid' ) ) {

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

        $arrCatalogs = [];
        $objQueryBuilderResults = $this->SQLQueryBuilder->execute( $arrQuery );
        $intResultRows = $objQueryBuilderResults->numRows;

        while ( $objQueryBuilderResults->next() ) {

            $arrCatalog = $objQueryBuilderResults->row();

            if ( $this->strMode === 'master' ) {

                $this->strMasterID = $arrCatalog['id'];
            }

            $arrCatalog['masterUrl'] = $this->getMasterRedirect( $arrCatalog, $arrCatalog['alias'] );

            if ( !empty( $this->arrViewPage ) ) {

                $arrCatalog['goBackLink'] = $this->generateUrl( $this->arrViewPage, '' );
                $arrCatalog['goBackLabel'] = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['back'];
            }

            if ( $this->catalogEnableFrontendEditing ) {

                $arrCatalog['operations'] = $this->generateOperations( $arrCatalog['id'], $arrCatalog['alias'], $arrCatalog );
            }

            if ( !empty( $arrCatalog ) && is_array( $arrCatalog ) ) {

                foreach ( $arrCatalog as $strFieldname => $varValue ) {

                    $arrCatalog[ $strFieldname ] = $this->parseCatalogValues( $varValue, $strFieldname, $arrCatalog );
                }
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

            $arrCatalog['timeFormat'] = $this->strTimeFormat;
            $arrCatalog['dateFormat'] = $this->strDateFormat;
            $arrCatalog['hasOperations'] = $this->blnHasOperations;
            $arrCatalog['catalogFields'] = $this->arrCatalogFields;
            $arrCatalog['dateTimeFormat'] = $this->strDateTimeFormat;
            $arrCatalog['catalogEntityFields'] = $this->arrEntityFields;
            $arrCatalog['readMore'] = $GLOBALS['TL_LANG']['MSC']['more'];
            $arrCatalog['activeFields'] = $this->getActiveCatalogFields();
            
            if ( $this->enableTableView && $this->strMode == 'view' ) {

                $arrCatalog['activeTableColumns'] = $this->catalogActiveTableColumns;
            }

            if ( $this->blnShowAsGroup ) {

                $strGroupName = $arrCatalog[ $this->catalogGroupBy ];

                if ( !isset( $this->arrGroups[ $strGroupName ] ) && $strGroupName ) {

                    $this->arrGroups[ $strGroupName ] = [];
                }
            }

            $arrCatalogs[] = $arrCatalog;
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

            shuffle( $arrCatalogs );
        }
        
        if ( $this->catalogUseArray ) {

            return $this->getArrayValue( $arrCatalogs, $intResultRows );
        }

        if ( $this->blnShowAsGroup ) {

            return $this->getGroupedValue( $arrCatalogs );
        }

        return $this->getTemplateValue( $arrCatalogs, $intResultRows );
    }


    protected function setHasOperationsFlag() {

        if ( !$this->catalogEnableFrontendEditing || empty( $this->catalogItemOperations ) ) {

            $this->blnHasOperations = false;

            return null;
        }

        if ( isset( $this->catalogItemOperations[0] ) && !Toolkit::isEmpty( $this->catalogItemOperations[0] ) ) {

            $this->blnHasOperations = true;
        }

        if ( count( $this->catalogItemOperations ) === 1 && in_array( 'create', $this->catalogItemOperations ) ) {

            $this->blnHasOperations = false;
        }
    }


    protected function getGroupedValue( $arrCatalogs ) {

        $arrIndexes = [];
        $objTemplate = new \FrontendTemplate( $this->strTemplate );

        foreach ( $arrCatalogs as $arrCatalog ) {

            $strGroupName = $arrCatalog[ $this->catalogGroupBy ];

            if ( !isset( $this->arrGroups[ $strGroupName ] ) ) $arrIndexes[ $strGroupName ] = 0;
            $arrCatalog['cssClass'] = $arrIndexes[ $strGroupName ] % 2 ? ' even' : ' odd';
            $objTemplate->setData( $arrCatalog );

            $this->arrGroups[ $strGroupName ][] = $objTemplate->parse();

            $arrIndexes[ $strGroupName ]++;
        }

        return $this->arrGroups;
    }


    protected function getTemplateValue( $arrCatalogs, $intResultRows ) {

        $strContent = '';
        $objTemplate = new \FrontendTemplate( $this->strTemplate );

        foreach ( $arrCatalogs as $intIndex => $arrCatalog ) {

            $arrCatalog['cssClass'] = $intIndex % 2 ? ' even' : ' odd';
            $arrCatalog['entityIndex'] = [ $intIndex + 1, $intResultRows ];

            if ( !$intIndex ) {

                $arrCatalog['cssClass'] .= ' first';
            }

            if ( $intIndex == ( $intResultRows - 1 ) ) {

                $arrCatalog['cssClass'] .= ' last';
            }

            $objTemplate->setData( $arrCatalog );
            $strContent .= $objTemplate->parse();
        }

        return $strContent;
    }


    protected function getArrayValue( $arrCatalogs, $intResultRows ) {

        for ( $intIndex = 0; $intIndex < count( $arrCatalogs ); $intIndex++ ) {

            $arrCatalogs[ $intIndex ]['cssClass'] = $intIndex % 2 ? ' even' : ' odd';
            $arrCatalogs[ $intIndex ]['entityIndex'] = [ $intIndex + 1, $intResultRows ];

            if ( !$intIndex ) {

                $arrCatalogs[ $intIndex ]['cssClass'] .= ' first';
            }

            if ( $intIndex == ( $intResultRows - 1 ) ) {

                $arrCatalogs[ $intIndex ]['cssClass'] .= ' last';
            }
        }

        return $arrCatalogs;
    }


    protected function getActiveFieldsHeadline( $strTemplate ) {

        return sprintf( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['activeFieldsHeadline'], $strTemplate );
    }


    protected function setOrderByParameters() {

        $strSort = $this->CatalogInput->getActiveValue( 'sortID' . $this->id );
        $strOrder = $this->CatalogInput->getActiveValue( 'orderID' . $this->id );

        if ( Toolkit::isEmpty( $strSort ) || is_array( $strSort ) ) {

            $strSort = '';
        }

        if ( Toolkit::isEmpty( $strOrder ) || is_array( $strOrder ) ) {

            $strOrder = 'DESC';

        } else {

            mb_strtoupper( $strOrder, 'UTF-8' );
        }

        if ( !in_array( $strOrder, [ 'ASC','DESC' ] ) ) {

            if ( $strOrder == 'RAND' ) {

                $this->catalogRandomSorting = '1';
            }

            else {

                $strOrder = 'DESC';
            }
        };

        if ( $strSort && $this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( $strSort, $this->catalogTablename ) ) {

            $this->catalogOrderBy = [[

                'key' => $strSort,
                'value' => $strOrder
            ]];
        }
    }


    protected function getMasterRedirect( $arrCatalog = [], $strAlias = '' ) {
        
        if ( $this->catalogDisableMasterLink ) return '';

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

        return $this->generateUrl( $this->arrMasterPage, $strAlias );
    }


    protected function getAliasWithParameters( $strAlias, $arrCatalog = [] ) {

        if ( !empty( $this->arrRoutingParameter ) && is_array( $this->arrRoutingParameter ) ) {

            $strAliasWithFragments = '';

            if ( !in_array( 'auto_item', $this->arrRoutingParameter ) ) {

                return $strAlias;
            }

            $intIndex = 0;
            $intTotal = count( $this->arrRoutingParameter ) - 1;
            $blnAutoItem = in_array( 'auto_item', $this->arrRoutingParameter );

            foreach ( $this->arrRoutingParameter as $strParameter ) {

                ++$intIndex;

                if ( $strParameter === 'auto_item' ) {

                    if ( Toolkit::isEmpty( $strAlias ) ) continue;

                    $strAliasWithFragments .= '/'. $strAlias;

                    continue;
                }

                if ( !Toolkit::isEmpty( $arrCatalog[ $strParameter ] ) ) {

                    $strAliasWithFragments .= $arrCatalog[ $strParameter ] . ( $intIndex != $intTotal && $blnAutoItem ? '/' : '' );
                }

                else {

                    $strAliasWithFragments .= ' ' . ( $intIndex != $intTotal && $blnAutoItem ? '/' : '' );
                }
            }

            if ( $strAliasWithFragments ) $strAlias = $strAliasWithFragments;
        }

        return $strAlias;
    }


    protected function isFastMode( $strType = '' ) {

        if ( $this->catalogFastMode && $this->strMode == 'view' && in_array( $strType, $this->arrDoNotRenderInFastMode ) ) {

            return true;
        }

        return false;
    }


    protected function parseCatalogValues( $varValue, $strFieldname, &$arrCatalog ) {

        $arrField = $this->arrCatalogFields[ $strFieldname ];
        $strType = $arrField['type'];

        if ( $this->isFastMode( $strType ) ) {

            return '';
        }

        switch ( $strType ) {

            case 'upload':

                if ( is_null( $varValue ) ) return $arrField['useArrayFormat'] ? [] : '';

                $varValue = Upload::parseValue( $varValue, $arrField, $arrCatalog );

                if ( is_array( $varValue ) && $arrField['fileType'] == 'gallery' ) {

                    if ( $varValue['preview'] ) {

                        $arrCatalog[ $strFieldname . 'Preview' ] = $varValue['preview'];
                    }

                    return $varValue['gallery'];
                }

                return $varValue;

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

            case 'date':

                return DateInput::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'number':

                return Number::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'textarea':

                return Textarea::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'dbColumn':

                return DbColumn::parseValue( $varValue, $arrField, $arrCatalog );

                break;
        }

        return $varValue;
    }


    public function getCommentForm( $strMasterID ) {

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

            ( $strMasterID ? $strMasterID : '0' ),

            []
        );
    }


    protected function setOptions() {

        if ( !empty( $this->arrOptions ) && is_array( $this->arrOptions ) ) {

            foreach ( $this->arrOptions as $strKey => $varValue ) {

                $this->{$strKey} = $varValue;
            }
        }
    }


    protected function getPageModel( $strID ) {

        return $this->SQLQueryHelper->SQLQueryBuilder->execute([

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


    protected function prepareJoinData () {

        $arrReturn = [];

        foreach ( $this->catalogJoinFields as $strFieldJoinID ) {

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

            $this->arrCatalogFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogTablename( $arrRelatedJoinData['onTable'], $this->arrCatalogFields, true );

            $arrReturn[] = $arrRelatedJoinData;
        }

        return $arrReturn;
    }


    protected function generateUrl( $objPage, $strAlias ) {

        if ( $objPage == null ) return '';

        return $this->generateFrontendUrl( $objPage, ( $strAlias ? '/' . $strAlias : '' ) );
    }


    protected function generateOperations( $strID, $strAlias = '', $arrCatalog = [] ) {

        $arrReturn = [];
        $this->loadLanguageFile( 'tl_module' );

        if ( !empty( $this->catalogItemOperations ) && is_array( $this->catalogItemOperations ) ) {

            $strAlias = $this->getAliasWithParameters( $strAlias, $arrCatalog );

            foreach ( $this->catalogItemOperations as $strOperation ) {

                if ( !$strOperation || $strOperation == 'create' ) continue;

                if ( !$this->FrontendEditingPermission->hasPermission( ( $strOperation === 'copy' ? 'create' : $strOperation  ), $this->catalogTablename ) ) {

                    continue;
                }

                $strActFragment = sprintf( '?act%s=%s&id%s=%s', $this->id, $strOperation, $this->id, $strID );

                if ( $this->arrCatalog['pTable'] ) {

                    $strActFragment .= sprintf( '&amp;pid=%s', ( \Input::get('pid' ) ? \Input::get('pid' ) : $arrCatalog['pid'] ) );
                }

                $arrReturn[ $strOperation ] = [

                    'href' => $this->generateUrl( $this->arrFrontendEditingPage, $strAlias ) . $strActFragment,
                    'title' => $GLOBALS['TL_LANG']['tl_module']['reference']['catalogItemOperations'][ $strOperation ],
                    'image' => \Image::getHtml( sprintf( 'system/modules/catalog-manager/assets/icons/%s.svg', $strOperation ), $GLOBALS['TL_LANG']['tl_module']['reference']['catalogItemOperations'][ $strOperation ] ),
                    'attributes' => $strOperation === 'delete' ? 'onclick="if(!confirm(\'' . sprintf( $GLOBALS['TL_LANG']['MSC']['deleteConfirm'], $strID ) . '\'))return false;"' : '',
                ];
            }
        }

        if ( empty( $arrReturn ) ) $this->blnHasOperations = false;

        return $arrReturn;
    }


    public function getCreateOperation() {

        $strPTableFragment = '';
        $this->loadLanguageFile( 'tl_module' );

        if ( !$this->catalogEnableFrontendEditing ) return [];

        if ( !$this->FrontendEditingPermission->hasPermission( 'create', $this->catalogTablename ) ) {

            return [];
        }

        if ( empty( $this->catalogItemOperations ) || !in_array( 'create', $this->catalogItemOperations ) ) {

            return [];
        }

        if ( $this->arrCatalog['pTable'] && !\Input::get('pid' ) ) {

            return [];
        }

        if ( $this->arrCatalog['pTable'] ) {

            $strPTableFragment = sprintf( '&amp;pid=%s', \Input::get('pid' ) );
        }

        return [

            'attributes' => '',
            'title' => $GLOBALS['TL_LANG']['tl_module']['reference']['catalogItemOperations']['create'],
            'href' => $this->generateUrl( $this->arrFrontendEditingPage, '' ) . sprintf( '?act%s=create%s', $this->id, $strPTableFragment ),
            'image' => \Image::getHtml( 'system/modules/catalog-manager/assets/icons/new.svg', $GLOBALS['TL_LANG']['tl_module']['reference']['catalogItemOperations']['create'] )
        ];
    }


    protected function preparePTableJoinData () {

        $this->arrCatalogFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogTablename( $this->arrCatalog['pTable'], $this->arrCatalogFields, true );

        return [

            'field' => 'pid',
            'onField' => 'id',
            'multiple' => false,
            'table' => $this->catalogTablename,
            'onTable' => $this->arrCatalog['pTable']
        ];
    }


    protected function setRelatedTableLinks( $strID ) {

        foreach ( $this->arrRelatedTables as $strTablename => $arrRelatedTable ) {

            $strUrl = $this->arrRelatedTables[ $strTablename ]['url'];
            $strSuffix = sprintf( '?pid=%s', $strID );

            $this->arrRelatedTables[ $strTablename ]['href'] = $strUrl . $strSuffix;
        }

        return $this->arrRelatedTables;
    }


    protected function setRelatedTables() {

        if ( !empty( $this->catalogRelatedChildTables )  && is_array( $this->catalogRelatedChildTables ) ) {

            foreach ( $this->catalogRelatedChildTables as $arrRelatedTable ) {

                if ( Toolkit::isEmpty( $arrRelatedTable['active'] ) ) continue;

                $arrTableData = [];
                $objCatalog = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( 'SELECT * FROM tl_catalog WHERE tablename = ?' )->limit(1)->execute( $arrRelatedTable['table'] );

                if ( !$objCatalog->numRows ) continue;

                $arrCatalog = $objCatalog->row();
                $strName = $this->I18nCatalogTranslator->getModuleLabel( $arrRelatedTable['table'] );
                $strTitle = $strName[0] ? $strName[0] : $arrCatalog['name'];

                $arrTableData['title'] = $strTitle;
                $arrTableData['info'] = $arrCatalog['info'];
                $arrTableData['description'] = $arrCatalog['description'];
                $arrTableData['url'] = \Controller::replaceInsertTags( $arrRelatedTable['pageURL'] );
                $arrTableData['image'] = \Image::getHtml( $this->IconGetter->setCatalogIcon( $arrRelatedTable['table'] ), $strTitle );

                $this->arrRelatedTables[ $arrRelatedTable['table'] ] = $arrTableData;
            }
        }
    }
}