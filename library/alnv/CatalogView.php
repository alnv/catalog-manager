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
    protected $arrParseAsArray = [];
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


    public function __construct() {

        parent::__construct();

        $this->import( 'IconGetter' );
        $this->import( 'CatalogInput' );
        $this->import( 'CatalogEvents' );
        $this->import( 'TemplateHelper' );
        $this->import( 'SQLQueryHelper' );
        $this->import( 'SQLQueryBuilder' );
        $this->import( 'CatalogFieldBuilder' );
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

        $this->CatalogFieldBuilder->initialize( $this->catalogTablename );

        $this->arrCatalog = $this->CatalogFieldBuilder->getCatalog();
        $this->arrCatalogFields = $this->CatalogFieldBuilder->getCatalogFields( false, $this );

        if ( !empty( $this->arrCatalogFields ) && is_array( $this->arrCatalogFields ) ) {

            foreach ( $this->arrCatalogFields as $strID => $arrField ) {

                if ( !$arrField['fieldname'] || !$arrField['type'] ) continue;

                $arrFieldLabels = $this->I18nCatalogTranslator->get( 'field', $arrField['fieldname'], [ 'table' => $this->catalogTablename, 'title' => $arrField['title'], 'description' =>  $arrField['description'] ] );

                $this->arrCatalogFields[ $strID ][ 'title' ] = $arrFieldLabels[0];
                $this->arrCatalogFields[ $strID ][ 'description' ] = $arrFieldLabels[1];

                if ( in_array( $arrField['type'], [ 'map', 'message' ] ) ) {

                    $this->arrCatalogStaticFields[] = $strID;
                }

                $this->setPreviewEntityFields( $arrField['fieldname'], $this->arrCatalogFields[ $strID ] );
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
        $this->catalogDownloads = Toolkit::deserialize( $this->catalogDownloads );
        $this->catalogTaxonomies = Toolkit::deserialize( $this->catalogTaxonomies );
        $this->catalogJoinFields = Toolkit::parseStringToArray( $this->catalogJoinFields );
        $this->catalogItemOperations = Toolkit::deserialize( $this->catalogItemOperations );
        $this->catalogJoinCTables = Toolkit::parseStringToArray( $this->catalogJoinCTables );
        $this->catalogRelatedChildTables = Toolkit::deserialize( $this->catalogRelatedChildTables );
        $this->catalogExcludeArrayOptions = Toolkit::deserialize( $this->catalogExcludeArrayOptions );
        $this->catalogPreventFieldFromFastMode = Toolkit::deserialize( $this->catalogPreventFieldFromFastMode );

        $this->setRelatedTables();

        if ( $objPage->catalogRoutingTable && $objPage->catalogRoutingTable !== $this->catalogTablename ) {

            $objPage->catalogUseRouting = '';
        }

        if ( $objPage->catalogUseRouting && $objPage->catalogRouting && !\Config::get('CTLG_IGNORE_LIST_ROUTING') ) {

            $this->arrRoutingParameter = Toolkit::getRoutingParameter( $objPage->catalogRouting );
        }

        if ( empty( $this->arrRoutingParameter ) && $this->catalogUseMasterPage ) {

            if ( $this->arrMasterPage['catalogUseRouting'] ) {

                $this->arrRoutingParameter = Toolkit::getRoutingParameter( $this->arrMasterPage['catalogRouting'] );
            }
        }

        if ( $this->enableTableView && $this->strMode == 'view' ) {

            $this->strTemplate = $this->catalogTableBodyViewTemplate;
            $this->catalogActiveTableColumns = $this->setActiveTableColumns();

            $this->objMainTemplate->activeTableColumns = $this->catalogActiveTableColumns;
            $this->objMainTemplate->hasRelations = $this->catalogUseRelation ? true : false;
            $this->objMainTemplate->hasDownloads = $this->catalogUseDownloads ? true : false;
            $this->objMainTemplate->readMoreColumnTitle = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['detailLink'];
            $this->objMainTemplate->sharingButtonsColumnTitle = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['sharing'];
            $this->objMainTemplate->downloadsColumnTitle = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['downloadLinks'];
            $this->objMainTemplate->relationsColumnTitle = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['relationsLinks'];
            $this->objMainTemplate->operationsColumnTitle = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['operationsLinks'];
        }

        $this->blnShowAsGroup = $this->catalogGroupBy && $this->strMode == 'view' ? true : false;

        $this->objMainTemplate->timeFormat = $this->strTimeFormat;
        $this->objMainTemplate->dateFormat = $this->strDateFormat;
        $this->objMainTemplate->catalogFields = $this->arrCatalogFields;
        $this->objMainTemplate->dateTimeFormat = $this->strDateTimeFormat;
        $this->objMainTemplate->catalogEntityFields = $this->arrEntityFields;

        $this->objMainTemplate->mapProtected = \Config::get('catalogMapProtected');
        $this->objMainTemplate->mapPrivacyText = \Controller::replaceInsertTags( \Config::get('catalogMapPrivacyText') );
        $this->objMainTemplate->mapPrivacyButtonText = \Controller::replaceInsertTags( ( \Config::get('catalogMapPrivacyButtonText') ?: $GLOBALS['TL_LANG']['MSC']['googleMapPrivacyAcceptText'] ) );

        $this->FrontendEditingPermission->blnDisablePermissions = $this->catalogEnableFrontendPermission ? false : true;

        if ( !$this->FrontendEditingPermission->blnDisablePermissions ) {

            $this->FrontendEditingPermission->initialize();
        }

        if ( $this->catalogUseSocialSharingButtons ) {

            $this->import( 'SocialSharingButtons' );
            $blnDefaultTheme = $this->catalogDisableSocialSharingCSS ? false : true;
            $arrSocialSharingButtons = Toolkit::deserialize( $this->catalogSocialSharingButtons );
            $this->SocialSharingButtons->initialize( $arrSocialSharingButtons, $this->catalogSocialSharingTemplate, $blnDefaultTheme, [

                'catalogSocialSharingCssID' => $this->catalogSocialSharingCssID,
                'catalogSocialSharingHeadline' => $this->catalogSocialSharingHeadline
            ]);
        }

        if ( \Input::get( 'toggleVisibility' . $this->id ) ) {

            $this->toggleVisibility();
        }

        if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerInitializeView'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerInitializeView'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['catalogManagerInitializeView'] as $arrCallback )  {

                if ( is_array( $arrCallback ) ) {

                    $this->import( $arrCallback[0] );
                    $this->{$arrCallback[0]}->{$arrCallback[1]}( $this );
                }
            }
        }

        $this->setHasOperationsFlag();
    }


    protected function toggleVisibility() {

        $strId = \Input::get( 'toggleVisibility' . $this->id );

        if ( $this->catalogTablename && $this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( 'invisible', $this->catalogTablename ) ) {

            $objEntity = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( 'SELECT invisible FROM '. $this->catalogTablename .' WHERE id = ?' )->limit(1)->execute( $strId );

            $strValue = $objEntity->invisible ? '' : '1';
            $dteTime = \Date::floorToMinute();

            $arrValues = [

                'tstamp' => $dteTime,
                'invisible' => $strValue

            ];

            $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( 'UPDATE '. $this->catalogTablename .' %s WHERE id = ?' )->set( $arrValues )->execute( $strId );

            $arrData = [

                'id' => $strId,
                'row' => $arrValues,
                'table' => $this->catalogTablename,
            ];

            $this->CatalogEvents->addEventListener( 'update', $arrData, $this );
        }
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

        global $objPage;

        $this->catalogOffset = (int) $this->catalogOffset;

        $blnActive = $this->catalogActiveParameters ? false : true;
        $intOffset = $this->catalogOffset;
        $strPageID = 'page_e' . $this->id;
        $intPerPage = intval( $this->catalogPerPage );
        $intPagination = intval( \Input::get( $strPageID ) );

        $arrQuery['table'] = $this->catalogTablename;
        $arrQuery['joins'] = [];
        $arrTaxonomies  = [];

        if ( !$this->catalogTablename || !$this->SQLQueryBuilder->tableExist( $this->catalogTablename ) ) return '';

        if (!empty($this->catalogJoinFields) || is_array($this->catalogJoinFields)) {
            $this->prepareJoinData($arrQuery['joins']);
        }

        if ($this->catalogJoinParentTable && $this->arrCatalog['pTable']) {

            $this->preparePTableJoinData($arrQuery['joins']);
        }

        if ( in_array( $this->strMode, [ 'view', 'master' ] ) && !empty( $this->catalogTaxonomies['query'] ) && is_array( $this->catalogTaxonomies['query'] ) && $this->catalogUseTaxonomies ) {

            $arrTaxonomies = Toolkit::parseQueries( $this->catalogTaxonomies['query'] );
        }

        array_insert( $arrQuery['where'], 0, $arrTaxonomies );

        if ( $this->hasVisibility() ) {

            $dteTime = \Date::floorToMinute();

            $arrQuery['where'][] = [

                'field' => 'tstamp',
                'operator' => 'gt',
                'value' => 0
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
            $strDistance = $this->CatalogInput->getActiveValue( 'rs_dstnc' );
            $arrRSAttributes = [ 'rs_cty', 'rs_strt', 'rs_pstl', 'rs_cntry', 'rs_strtn' ];

            if ( Toolkit::isEmpty( $strDistance ) || is_array( $strDistance ) ) {

                $strDistance = '50';
            }

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
                $arrCords = $objGeoCoding->getCords( '', 'en', true );

                if ( $arrCords['lat'] && $arrCords['lng'] ) {

                    $arrQuery['distance'] = [

                        'value' => $strDistance,
                        'latCord' => $arrCords['lat'],
                        'lngCord' => $arrCords['lng'],
                        'latField' => $this->catalogFieldLat,
                        'lngField' => $this->catalogFieldLng
                    ];

                    $this->arrCatalogMapViewOptions['lat'] = $arrCords['lat'];
                    $this->arrCatalogMapViewOptions['lng'] = $arrCords['lng'];
                }
            }

            if ( !isset( $arrQuery['distance'] ) && $this->CatalogInput->getActiveValue( '_latitude' ) && $this->CatalogInput->getActiveValue( '_longitude' ) ) {

                $arrQuery['distance'] = [

                    'value' => $strDistance,
                    'latCord' => $this->CatalogInput->getActiveValue( '_latitude' ),
                    'lngCord' => $this->CatalogInput->getActiveValue( '_longitude' ),
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

        if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerViewQuery'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerViewQuery'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['catalogManagerViewQuery'] as $arrCallback )  {

                if ( is_array( $arrCallback ) ) {

                    $this->import( $arrCallback[0] );
                    $arrQuery = $this->{$arrCallback[0]}->{$arrCallback[1]}( $arrQuery, $this );
                }
            }
        }

        if ( $this->catalogActiveParameters ) {

            $arrActiveParameterFields = explode( ',', $this->catalogActiveParameters );

            foreach ( $arrActiveParameterFields as $strFieldname ) {

                if ( $this->CatalogInput->getActiveValue( $strFieldname ) !== '' && $this->CatalogInput->getActiveValue( $strFieldname ) !== null ) {

                    $blnActive = true;

                    break;
                }
            }
        }

        if ( !$blnActive ) {

            if ( $this->blnGoogleMapScript ) {

                $GLOBALS['TL_HEAD']['CatalogManagerGoogleMaps'] = Map::generateGoogleMapJSInitializer();
            }

            if ( $this->catalogUseArray || $this->blnShowAsGroup ) {

                return [];
            }

            return '';
        }

        $intTotal = $this->SQLQueryBuilder->execute( $arrQuery )->count();

        if ( $this->strMode == 'view' ) {

            $arrQuery['pagination'] = [

                'limit' => $this->catalogPerPage,
                'offset' => $this->catalogOffset
            ];
        }

        if ( $this->catalogOffset ) $intTotal -= $intOffset;

        if ( \Input::get( $strPageID ) && $this->catalogAddPagination ) {

            $intOffset = $intPagination;

            if ( $intPerPage > 0 && $this->catalogOffset ) {

                $intOffset += round( $this->catalogOffset / $intPerPage );
            }

            $arrQuery['pagination']['offset'] = ( $intOffset - 1 ) * $intPerPage;
        }

        $arrCatalogs = [];
        $intCurrentEntity = 0;
        $objEntities = $this->SQLQueryBuilder->execute($arrQuery);
        $intNumRows = $objEntities->numRows;

        if ( $this->strMode == 'view' ) $this->objMainTemplate->entityIndex = [ $intNumRows, $intTotal ];

        while ( $objEntities->next() ) {

            $arrCatalog = $objEntities->row();
            $intCurrentEntity++;

            $arrCatalog['useSocialSharingButtons'] = $this->catalogUseSocialSharingButtons ? true : false;
            $arrCatalog['origin'] = $arrCatalog;

            if ( $this->strMode === 'master' ) {

                $this->strMasterID = $arrCatalog['id'];
            }

            $arrCatalog['masterUrl'] = $this->getMasterRedirect( $arrCatalog, $arrCatalog['alias'] );
            $arrCatalog['hasGoBackLink'] = $this->catalogUseViewPage && $this->catalogViewPage !== '0';

            if ( !empty( $this->arrViewPage ) ) {

                $arrCatalog['goBackLink'] = $this->generateUrl( $this->arrViewPage, '' );
                $arrCatalog['goBackLabel'] = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['back'];
            }

            if ( $this->catalogEnableFrontendEditing ) {

                $arrCatalog['operations'] = $this->generateOperations( $arrCatalog['id'], $arrCatalog['alias'], $arrCatalog );
            }

            if ( $this->catalogUseDownloads ) {

                $arrCatalog['downloads'] = $this->generateDownloads( $arrCatalog['id'], $arrCatalog['alias'] );
            }

            if ( !empty( $arrCatalog ) && is_array( $arrCatalog ) ) {

                if ( $arrCatalog['id'] && !empty( $this->catalogJoinCTables ) ) {

                    foreach ( $this->catalogJoinCTables as $strTable ) {

                        $arrCatalog[ $strTable ] = $this->getChildrenByIdAndTable( $arrCatalog['id'], $strTable );
                    }
                }

                foreach ( $arrCatalog as $strFieldname => $varValue ) {

                    if ( isset( $this->arrParseAsArray[ $strFieldname ] ) ) {

                        $arrCatalog[ $strFieldname ] = $this->getJoinedEntities( $varValue, $strFieldname );

                        continue;
                    }

                    $arrCatalog[ $strFieldname ] = $this->parseCatalogValues( $varValue, $strFieldname, $arrCatalog );
                }
            }

            if ( $this->catalogUseRelation ) {

                $arrCatalog['relations'] = $this->setRelatedTableLinks( $arrCatalog['id'] );
            }

            $arrCatalog['contentElements'] = '';

            if ( ( $this->strMode === 'master' || $this->catalogAddContentElements ) && $this->arrCatalog['addContentElements'] ) {

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

                            if ( !$this->blnGoogleMapScript ) $this->blnGoogleMapScript = true;

                            $arrCatalog[ $strID ] = Map::parseValue( '', $arrField, $arrCatalog );

                            break;

                        case 'message':

                            $arrCatalog[ $strID ] = MessageInput::parseValue( '', $arrField, $arrCatalog );

                            break;
                    }
                }
            }

            if ( $this->blnMapViewMode ) {

                $this->arrCatalogMapViewOptions['mapInfoBoxContent'] = Map::parseInfoBoxContent( $this->catalogMapInfoBoxContent, $arrCatalog );
                $this->arrCatalogMapViewOptions['locationLat'] = $arrCatalog[ $this->catalogFieldLat ];
                $this->arrCatalogMapViewOptions['locationLng'] = $arrCatalog[ $this->catalogFieldLng ];

                $arrCatalog['map'] = $this->arrCatalogMapViewOptions;
            }

            if ( $this->strMode == 'master' ) {

                if ( $this->catalogSEOTitle ) $objPage->pageTitle = $arrCatalog[$this->catalogSEOTitle] ? strip_tags( $arrCatalog[$this->catalogSEOTitle] ) : $objPage->pageTitle;
                if ( $this->catalogSEODescription ) $objPage->description = $arrCatalog[$this->catalogSEODescription] ? strip_tags( $arrCatalog[$this->catalogSEODescription] ) : $objPage->description;
            }

            $arrCatalog['_moduleId'] = $this->id;
            $arrCatalog['timeFormat'] = $this->strTimeFormat;
            $arrCatalog['dateFormat'] = $this->strDateFormat;
            $arrCatalog['hasOperations'] = $this->blnHasOperations;
            $arrCatalog['catalogFields'] = $this->arrCatalogFields;
            $arrCatalog['dateTimeFormat'] = $this->strDateTimeFormat;
            $arrCatalog['catalogEntityFields'] = $this->arrEntityFields;
            $arrCatalog['readMore'] = $GLOBALS['TL_LANG']['MSC']['more'];

            if ( $this->strMode == 'view' ) {

                $intPageNumber = $intPagination - 1;
                $intPageOffset = !$this->catalogOffset ? $intPerPage : $this->catalogOffset;
                $intMultiplicator = $intPageNumber > 0 ? $intPageOffset * $intPageNumber : 0;
                $arrCatalog['entityIndex'] = [ $intCurrentEntity + $intMultiplicator, $intTotal ];
            }

            if ( $this->enableTableView && $this->strMode == 'view' ) {

                $arrCatalog['activeTableColumns'] = $this->catalogActiveTableColumns;
            }

            if ( $this->blnShowAsGroup && !$this->enableTableView ) {

                $this->createGroups( $arrCatalog[ $this->catalogGroupBy ] );
            }

            if ( $arrCatalog['useSocialSharingButtons'] ) {

                $arrCatalog['socialSharingButtons'] = $this->SocialSharingButtons->render( $arrCatalog, $this->catalogSEOTitle, $this->catalogSEODescription );
            }

            if ( isset($GLOBALS['TL_HOOKS']['catalogManagerRenderCatalog']) && is_array($GLOBALS['TL_HOOKS']['catalogManagerRenderCatalog'])) {
                foreach ($GLOBALS['TL_HOOKS']['catalogManagerRenderCatalog'] as $arrCallback)  {
                    if ( is_array( $arrCallback ) ) {
                        $this->import( $arrCallback[0] );
                        $this->{$arrCallback[0]}->{$arrCallback[1]}( $arrCatalog, $this->catalogTablename, $this);
                    }
                }
                if (empty($arrCatalog)) {
                    continue;
                }
            }

            if ( $this->catalogUseArray ) {

                if ( in_array( 'origin', $this->catalogExcludeArrayOptions ) ) unset( $arrCatalog['origin'] );
                if ( in_array( 'catalogFields', $this->catalogExcludeArrayOptions ) ) unset( $arrCatalog['catalogFields'] );
                if ( in_array( 'catalogEntityFields', $this->catalogExcludeArrayOptions ) ) unset( $arrCatalog['catalogEntityFields'] );
            }

            $arrCatalogs[] = $arrCatalog;
        }
        
        if ( $intPerPage > 0 && $this->catalogAddPagination && $this->strMode == 'view' ) {

            $this->objMainTemplate->pagination = $this->TemplateHelper->addPagination( $intTotal, $intPerPage, $strPageID, $this->arrViewPage['id'] );
        }

        if ( $this->blnGoogleMapScript ) {

            $GLOBALS['TL_HEAD']['CatalogManagerGoogleMaps'] = Map::generateGoogleMapJSInitializer();
        }

        if ( $this->catalogRandomSorting ) shuffle( $arrCatalogs );
        if ( $this->catalogUseArray ) return $this->getArrayValue( $arrCatalogs, $intNumRows );
        if ( $this->blnShowAsGroup && !$this->enableTableView ) return $this->getGroupedValue( $arrCatalogs );

        return $this->getTemplateValue( $arrCatalogs, $intNumRows );
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


    protected function getJoinedEntities( $strValue, $strFieldname ) {

        $arrReturn = [];
        $strTable = $this->arrParseAsArray[ $strFieldname ]['onTable'];
        $strField = $this->arrParseAsArray[ $strFieldname ]['onField'];
        $arrOrderBy= Toolkit::parseStringToArray( $this->arrCatalogFields[ $strFieldname ]['dbOrderBy'] );

        $arrQuery = [

            'table' => $strTable,
            'where' => [

                [
                    'field' => $strField,
                    'operator' => 'findInSet',
                    'value' => explode( ',', $strValue )
                ]
            ],
            'orderBy' => []
        ];

        if ( is_array( $arrOrderBy ) && !empty( $arrOrderBy ) ) {

            foreach ( $arrOrderBy as $arrOrder ) {

                $arrQuery['orderBy'][] = [

                    'field' => $arrOrder['key'],
                    'order' => $arrOrder['value']
                ];
            }
        }

        if (  $this->arrParseAsArray[ $strFieldname ]['hasVisibility'] ) {

            $dteTime = \Date::floorToMinute();

            $arrQuery['where'][] = [

                'field' => 'tstamp',
                'operator' => 'gt',
                'value' => 0
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

        $objEntities = $this->SQLQueryBuilder->execute( $arrQuery );

        if ( !$objEntities->numRows ) return $arrReturn;

        while ( $objEntities->next() ) {

            $arrReturn[] = Toolkit::parseCatalogValues( $objEntities->row(), $this->arrCatalogFields, false, $strTable );
        }

        return $arrReturn;
    }


    public function hasVisibility() {

        if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( 'invisible', $this->catalogTablename ) ) {

            return false;
        }

        if ( $this->catalogIgnoreVisibility && $this->catalogEnableFrontendEditing ) {

            return false;
        }

        if ( !is_array( $this->arrCatalog['operations'] ) ) {

            return false;
        }

        if ( !in_array( 'invisible', $this->arrCatalog['operations'] ) ) {

            return false;
        }

        if ( BE_USER_LOGGED_IN ) {

            return false;
        }

        return true;
    }


    protected function createGroups( $varGroupName ) {

        if ( is_array( $varGroupName ) ) {

            foreach ( $varGroupName as $strGroup ) {

                if ( is_array( $strGroup ) ) {

                    $strKeyname = $this->arrCatalogFields[ $this->catalogGroupBy ]['dbTableValue'] ?: 'title';
                    $strGroup = $strGroup[ $strKeyname ];
                }

                $this->createGroups( $strGroup );
            }
        }

        if ( $varGroupName && is_string( $varGroupName ) && !isset( $this->arrGroups[ $varGroupName ] ) ) {

            $this->arrGroups[ $varGroupName ] = [];
        }
    }


    protected function getGroupedValue( $arrCatalogs ) {

        $arrIndexes = [];

        foreach ( $arrCatalogs as $arrCatalog ) {

            $varGroupName = $arrCatalog[ $this->catalogGroupBy ];
            $this->groupByValue( $varGroupName, $arrCatalog, $arrIndexes );
        }

        return $this->arrGroups;
    }


    protected function groupByValue( $varGroupName, $arrCatalog, &$arrIndexes ) {

        if ( is_array( $varGroupName ) ) {

            foreach ( $varGroupName as $strGroupName ) {

                if ( is_array( $strGroupName ) ) {

                    $strKeyname = $this->arrCatalogFields[ $this->catalogGroupBy ]['dbTableValue'] ?: 'title';
                    $strGroupName = $strGroupName[ $strKeyname ];
                }

                $this->groupByValue( $strGroupName, $arrCatalog, $arrIndexes );
            }
        }

        if ( $varGroupName && is_string( $varGroupName ) ) {

            $objTemplate = new \FrontendTemplate( $this->strTemplate );

            if ( !$this->arrGroups[ $varGroupName ] ) $arrIndexes[ $varGroupName ] = 0;

            $arrCatalog['cssClass'] = $arrIndexes[ $varGroupName ] % 2 ? ' even' : ' odd';
            $arrCatalog['_mainGroup'] = $varGroupName;
            $objTemplate->setData($arrCatalog);

            $this->arrGroups[ $varGroupName ][] = $objTemplate->parse();
            $arrIndexes[ $varGroupName ]++;
        }
    }


    protected function getTemplateValue( $arrCatalogs, $intNumRows ) {

        $strContent = '';
        $objTemplate = new \FrontendTemplate( $this->strTemplate );

        foreach ( $arrCatalogs as $intIndex => $arrCatalog ) {

            $arrCatalog['cssClass'] = $intIndex % 2 ? ' even' : ' odd';

            if ( !$intIndex ) $arrCatalog['cssClass'] .= ' first';
            if ( $intIndex == ( $intNumRows - 1 ) ) $arrCatalog['cssClass'] .= ' last';

            $objTemplate->setData( $arrCatalog );

            $strContent .= $objTemplate->parse();
        }

        return $strContent;
    }


    protected function getArrayValue( $arrCatalogs, $intNumRows ) {

        for ( $intIndex = 0; $intIndex < count( $arrCatalogs ); $intIndex++ ) {

            $arrCatalogs[ $intIndex ]['cssClass'] = $intIndex % 2 ? ' even' : ' odd';

            if ( !$intIndex ) $arrCatalogs[ $intIndex ]['cssClass'] .= ' first';
            if ( $intIndex == ( $intNumRows - 1 ) )  $arrCatalogs[ $intIndex ]['cssClass'] .= ' last';
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

            return Toolkit::generateAliasWithRouting( $strAlias, $this->arrRoutingParameter, $arrCatalog );
        }

        return $strAlias;
    }


    protected function isFastMode( $strType = '', $strFieldname = '' ) {

        if ( $this->catalogFastMode && $this->strMode == 'view' && in_array( $strType, Toolkit::$arrDoNotRenderInFastMode ) ) {

            if ( $strFieldname && is_array( $this->catalogPreventFieldFromFastMode ) && in_array( $strFieldname, $this->catalogPreventFieldFromFastMode )  ) return false;

            return true;
        }

        return false;
    }


    protected function parseCatalogValues( $varValue, $strFieldname, &$arrCatalog ) {

        $arrField = $this->arrCatalogFields[ $strFieldname ];
        $strType = $arrField['type'];

        if ( $arrField['_isDate'] != null && $arrField['_isDate'] === true ) $strType = 'date';
        if ( Toolkit::isEmpty( $strType ) ) return $varValue;
        if ( $this->isFastMode( $strType, $strFieldname ) ) return '';

        // Override image size by module
        if ( !empty( $this->arrOptions['imgSize'] ) ) $arrField['size'] = $this->arrOptions['imgSize'];

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

            case 'text':

                return Text::parseValue( $varValue, $arrField, $arrCatalog );

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


    protected function prepareJoinData(&$arrReturn) {

        foreach ($this->catalogJoinFields as $strFieldJoinID) {

            $arrRelatedJoinData = [];

            if (!$this->arrCatalogFields[$strFieldJoinID]) {
                continue;
            }

            $arrRelatedJoinData['multiple'] = false;
            $arrRelatedJoinData['table'] = $this->catalogTablename;
            $arrRelatedJoinData['field'] = $this->arrCatalogFields[ $strFieldJoinID ]['fieldname'];
            $arrRelatedJoinData['onTable'] = $this->arrCatalogFields[ $strFieldJoinID ]['dbTable'];
            $arrRelatedJoinData['onField'] = $this->arrCatalogFields[ $strFieldJoinID ]['dbTableKey'];

            if ($this->arrCatalogFields[ $strFieldJoinID ]['multiple'] || $this->arrCatalogFields[ $strFieldJoinID ]['type'] == 'checkbox') {
                $arrRelatedJoinData['multiple'] = true;
            }

            $this->arrCatalogFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogTablename( $arrRelatedJoinData['onTable'], $this->arrCatalogFields, true, $this->arrCatalogStaticFields );

            if ($arrRelatedJoinData['multiple'] && $this->catalogJoinAsArray) {

                $objCatalogFieldBuilder = new CatalogFieldBuilder();
                $objCatalogFieldBuilder->initialize($arrRelatedJoinData['onTable']);

                $arrCatalog = $objCatalogFieldBuilder->getCatalog();
                $arrRelatedJoinData['hasVisibility'] = in_array('invisible', $arrCatalog['operations']);

                $this->arrParseAsArray[$arrRelatedJoinData['field']] = $arrRelatedJoinData;

                continue;
            }

            $arrReturn[] = $arrRelatedJoinData;
        }
    }


    protected function getChildrenByIdAndTable( $strId, $strTable ) {

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize( $strTable );

        $arrReturn = [];
        $arrCatalog = $objFieldBuilder->getCatalog();
        $arrFields = $objFieldBuilder->getCatalogFields( true, null );

        $arrQuery = [

            'table' => $strTable,
            'where' => [
                [
                    'field' => 'pid',
                    'value' => $strId,
                    'operator' => 'equal'
                ]
            ]
        ];

        if ( in_array( 'invisible', $arrCatalog['operations'] ) ) {

            $dteTime = \Date::floorToMinute();

            $arrQuery['where'][] = [

                'field' => 'tstamp',
                'operator' => 'gt',
                'value' => 0
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

        if ( !empty( $arrCatalog['sortingFields'] ) ) {

            $numFlag = (int) $arrCatalog['flag'] ?: 1;

            foreach ( $arrCatalog['sortingFields'] as $strSortingField ) {

                $arrQuery['orderBy'][] = [

                    'field' => $strSortingField,
                    'order' => ( $numFlag % 2 == 0 ) ? 'DESC' : 'ASC'
                ];
            }
        }

        $objEntities = $this->SQLQueryBuilder->execute( $arrQuery );

        if ( !$objEntities->numRows ) {

            return $arrReturn;
        }

        while ( $objEntities->next() ) {

            $arrReturn[] = Toolkit::parseCatalogValues( $objEntities->row(), $arrFields );
        }

        return $arrReturn;
    }


    protected function generateUrl( $objPage, $strAlias ) {

        if ( $objPage == null ) return '';

        return $this->generateFrontendUrl( $objPage, ( $strAlias ? '/' . $strAlias : '' ) );
    }


    protected function generateDownloads( $strID, $strAlias = '' ) {

        $arrReturn = [];
        $strConnector = '?';
        $strUrl = ampersand( \Environment::get('indexFreeRequest') );

        if ( strpos( $strUrl, $strConnector ) !== false ) {

            $strConnector = '&';
        }

        if ( !empty( $this->catalogDownloads ) && is_array( $this->catalogDownloads ) ) {

            foreach ( $this->catalogDownloads as $strDownload ) {

                $arrReturn[ $strDownload ] = [

                    'href' =>  $strUrl . $strConnector . $strDownload . $this->id . '=' . $strID,
                    'title' => $GLOBALS['TL_LANG']['tl_module']['reference']['catalogDownloadTitles'][ $strDownload ],
                    'image' => \Image::getHtml( Toolkit::getIcon( $strDownload ), $GLOBALS['TL_LANG']['tl_module']['reference']['catalogDownloadTitles'][ $strDownload ] ),
                    'attributes' => '',
                ];
            }
        }

        return $arrReturn;
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

                    'class' => 'act_' . $strOperation,
                    'href' => $this->generateUrl( $this->arrFrontendEditingPage, $strAlias ) . $strActFragment,
                    'title' => $GLOBALS['TL_LANG']['tl_module']['reference']['catalogItemOperations'][ $strOperation ],
                    'image' => \Image::getHtml( Toolkit::getIcon( $strOperation ), $GLOBALS['TL_LANG']['tl_module']['reference']['catalogItemOperations'][ $strOperation ] ),
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
            'image' => \Image::getHtml( Toolkit::getIcon( 'new' ), $GLOBALS['TL_LANG']['tl_module']['reference']['catalogItemOperations']['create'] )
        ];
    }


    protected function preparePTableJoinData ( &$arrReturn ) {

        $this->arrCatalogFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogTablename( $this->arrCatalog['pTable'], $this->arrCatalogFields, true, $this->arrCatalogStaticFields );

        $arrReturn[] = [

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

                if ( !is_array( $arrRelatedTable ) ) continue;

                if ( Toolkit::isEmpty( $arrRelatedTable['active'] ) ) continue;

                $arrTableData = [];
                $objCatalog = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( 'SELECT * FROM tl_catalog WHERE tablename = ?' )->limit(1)->execute( $arrRelatedTable['table'] );

                if ( !$objCatalog->numRows ) continue;

                $arrCatalog = $objCatalog->row();
                $strTitle = $this->I18nCatalogTranslator->get( 'module', $arrRelatedTable['table'], [ 'titleOnly' => true ] );

                $arrTableData['title'] = $strTitle;
                $arrTableData['info'] = $arrCatalog['info'];
                $arrTableData['description'] = $arrCatalog['description'];
                $arrTableData['url'] = \Controller::replaceInsertTags( $arrRelatedTable['pageURL'] );
                $arrTableData['image'] = \Image::getHtml( $this->IconGetter->setCatalogIcon( $arrRelatedTable['table'] ), $strTitle );

                $this->arrRelatedTables[ $arrRelatedTable['table'] ] = $arrTableData;
            }
        }
    }


    public function getCatalog() {

        return is_array( $this->arrCatalog ) && !empty( $this->arrCatalog ) ? $this->arrCatalog : [];
    }


    public function changeItemOperations( $arrItemOperations ) {

        $this->catalogItemOperations = $arrItemOperations;
    }


    public function getItemOperations() {

        return $this->catalogItemOperations;
    }
}