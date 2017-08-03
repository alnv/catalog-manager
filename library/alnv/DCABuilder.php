<?php

namespace CatalogManager;

class DCABuilder extends CatalogController {


    protected $strID;
    protected $strTable;
    protected $arrFields = [];
    protected $arrCatalog = [];
    protected $arrErrorTables = [];
    protected $strPermissionType = '';

    protected $arrOperations = [

        'cut' => false,
        'copy' => false,
        'invisible' => false
    ];


    public function __construct( $arrCatalog ) {

        parent::__construct();

        $this->import( 'Database' );
        $this->import( 'IconGetter' );
        $this->import( 'IconGetter' );
        $this->import( 'DCABuilderHelper' );
        $this->import( 'I18nCatalogTranslator' );

        $this->arrCatalog = $arrCatalog;
        $this->strID = $arrCatalog['id'];
        $this->strTable = $arrCatalog['tablename'];
        $this->strPermissionType = $arrCatalog['permissionType'];

        if ( !$this->strTable ) return null;

        if ( \Input::get( 'do' ) && \Input::get( 'do' ) == $this->arrCatalog['tablename'] ) {

            $objReviseRelatedTables = new ReviseRelatedTables();

            if ( $objReviseRelatedTables->reviseCatalogTables( $this->arrCatalog['tablename'] , $this->arrCatalog['pTable'], $this->arrCatalog['cTables'] ) ) {

                foreach ( $objReviseRelatedTables->getErrorTables() as $strTable ) {

                    \Message::addError( sprintf( "Table '%s' can not be used as relation. Please delete all rows or create valid pid value.", $strTable ) );

                    $this->arrErrorTables[] = $strTable;

                    if ( $strTable == $this->arrCatalog['pTable'] ) {

                        $this->arrCatalog['pTable'] = '';
                    }

                    if ( in_array( $strTable , $this->arrCatalog['cTables'] ) ) {

                        $intPosition = array_search( $strTable, $this->arrCatalog['cTables'] );

                        unset( $this->arrCatalog['cTables'][ $intPosition ] );
                    }
                }
            }
        }
    }
    

    public function initializeI18n() {

        $this->I18nCatalogTranslator->initialize();
    }


    protected function determineOperations() {

        $arrOperations = [];

        if ( $this->arrCatalog['operations'] ) {

            $arrOperations = deserialize( $this->arrCatalog['operations'] );
        }

        if ( !empty( $arrOperations ) && is_array( $arrOperations ) ) {

            foreach ( $arrOperations as $strOperation ) {

                $this->arrOperations[ $strOperation ] = isset( $this->arrOperations[ $strOperation ] );
            }
        }
    }


    protected function getDCAFields() {

        $objCatalogFieldsDb = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE `pid` = ? AND invisible != ? ORDER BY `sorting`' )->execute( $this->strID, "1" );

        while ( $objCatalogFieldsDb->next() ) {

            $strFieldname = $objCatalogFieldsDb->fieldname ? $objCatalogFieldsDb->fieldname : $objCatalogFieldsDb->id;

            if ( !$this->Database->fieldExists( $objCatalogFieldsDb->fieldname, $this->strTable ) ) {

                if ( !in_array( $objCatalogFieldsDb->type, $this->DCABuilderHelper->arrNoFieldnameRequired ) ) {

                    continue;
                }
            }

            $this->arrFields[ $strFieldname ] = $objCatalogFieldsDb->row();
        }
    }


    public function createDCA() {

        $this->initializeI18n();
        $this->determineOperations();
        $this->getDCAFields();

        $GLOBALS['TL_DCA'][ $this->strTable ] = [

            'config' => $this->createConfigDataArray(),

            'list' => [

                'label' => $this->createLabelDataArray(),
                'sorting' => $this->createSortingDataArray(),
                'operations' => $this->createOperationDataArray(),
                'global_operations' => $this->createGlobalOperationDataArray(),
            ],

            'palettes' => $this->createPaletteDataArray(),
            'fields' => $this->parseDCAFields()
        ];

        $GLOBALS['TL_LANG'][ $this->strTable ]['new'] = $this->I18nCatalogTranslator->getNewLabel();
        $GLOBALS['TL_LANG'][ $this->strTable ]['show'] = $this->I18nCatalogTranslator->getShowLabel();
    }


    protected function parseDCAFields() {

        $arrDCAFields = $this->getDefaultDCFields();

        if ( !empty( $this->arrFields ) && is_array( $this->arrFields ) ) {

            $arrDCAFields = $this->DCABuilderHelper->convertCatalogFields2DCA( $this->arrFields, $arrDCAFields, $this->arrCatalog );
        }

        return $arrDCAFields;
    }


    protected function getDefaultDCFields() {

        $arrReturn = $this->DCABuilderHelper->getPredefinedDCFields();

        if ( !$this->arrOperations['invisible'] ) {

            unset( $arrReturn['invisible'] );
        }

        if ( $this->arrCatalog['mode'] == '4' ) {

            $arrReturn['sorting'] = [

                'sql' => "int(10) unsigned NOT NULL default '0'"
            ];
        }

        if ( $this->arrCatalog['pTable'] ) {

            $arrReturn['pid'] = [

                'sql' => "int(10) unsigned NOT NULL default '0'",
            ];

            if ( !$this->shouldBeUsedParentTable() ) {

                $arrReturn['pid']['foreignKey'] = sprintf( '%s.id', $this->arrCatalog['pTable'] );
                $arrReturn['pid']['relation'] = [

                    'type' => 'belongsTo',
                    'load' => 'eager'
                ];
            }
        }

        if ( $arrReturn['alias'] && is_array( $arrReturn['alias'] ) ) {

            $arrReturn['alias']['save_callback'] = [ function( $varValue, \DataContainer $dc ) {

                $objDCACallbacks = new DCACallbacks();
                return $objDCACallbacks->generateAlias( $varValue, $dc, 'title', $this->strTable );
            }];
        }

        return $arrReturn;
    }


    protected function createConfigDataArray() {

        $arrReturn = [

            'label' => $this->I18nCatalogTranslator->getModuleTitle( $this->strTable ),
            'dataContainer' => 'Table',

            'oncut_callback' => [],
            'onload_callback' => [],
            'onsubmit_callback' => [],
            'ondelete_callback' => [],

            'sql' => [

                'keys' => [

                    'id' => 'primary'
                ]
            ]
        ];

        if ( $this->arrCatalog['useGeoCoordinates'] ) {

            $arrReturn['onsubmit_callback'][] = [ 'CatalogManager\DCACallbacks', 'generateGeoCords' ];
        }

        foreach ( $this->arrFields as $arrField ) {

            if ( !$arrField['useIndex'] ) continue;

            $arrReturn['sql']['keys'][ $arrField['fieldname'] ] = $arrField['useIndex'];
        }

        if ( $this->shouldBeUsedParentTable() ) {

            $arrReturn['ptable'] = $this->arrCatalog['pTable'];
        }

        if ( $this->arrCatalog['addContentElements'] ) {

            if ( !is_array( $this->arrCatalog['cTables'] ) ) {

                $this->arrCatalog['cTables'] = [];
            }

            $this->arrCatalog['cTables'][] = 'tl_content';
        }

        if ( !empty( $this->arrCatalog['cTables'] ) && is_array( $this->arrCatalog['cTables'] ) ) {

            $arrReturn['ctable'] = $this->arrCatalog['cTables'];
        }

        if ( $this->arrCatalog['permissionType'] ) {

            $arrReturn['onload_callback'][] = function() {

                $objDCAPermission = new DCAPermission();
                $objDCAPermission->checkPermission( $this->strTable , $this->strTable, $this->strTable . 'p', $this->strPermissionType );
            };
        }

        $arrReturn['oncut_callback'][] = [ 'CatalogManager\DCACallbacks', 'onCutCallback' ];
        $arrReturn['onsubmit_callback'][] = [ 'CatalogManager\DCACallbacks', 'onSubmitCallback' ];
        $arrReturn['ondelete_callback'][] = [ 'CatalogManager\DCACallbacks', 'onDeleteCallback' ];

        return $arrReturn;
    }


    protected function createLabelDataArray() {

        $arrReturn = [

            'showColumns' => $this->arrCatalog['showColumns'] ? true : false,
            'fields' => empty( $this->arrCatalog['labelFields'] ) ? [ 'title' ] : $this->arrCatalog['labelFields'],
        ];

        if ( $this->arrCatalog['format'] ) {

            $arrReturn['format'] = $this->arrCatalog['format'];
        }

        if ( $this->arrCatalog['useOwnLabelFormat'] && !Toolkit::isEmpty( $this->arrCatalog['labelFormat'] ) ) {

            $arrReturn['label_callback'] = function ( $arrRow, $strLabel ) {

                $objDCACallback = new DCACallbacks();
                
                return $objDCACallback->labelCallback( $this->arrCatalog['labelFormat'], $this->arrFields, $arrRow, $strLabel );
            };
        }

        if ( $this->arrCatalog['useOwnGroupFormat'] && !Toolkit::isEmpty( $this->arrCatalog['groupFormat'] ) && $this->arrCatalog['mode'] != '5' ) {

            $arrReturn['group_callback'] = function ( $strGroup, $strMode, $strField, $arrRow, $dc ) {

                $objDCACallback = new DCACallbacks();

                return $objDCACallback->groupCallback( $this->arrCatalog['groupFormat'], $this->arrFields, $strGroup, $strMode, $strField, $arrRow, $dc );
            };
        }

        if ( $this->arrCatalog['mode'] == '5' ) {

            $arrReturn['label_callback'] = function( $arrRow, $strLabel, \DataContainer $dc = null, $strImageAttribute = '', $blnReturnImage = false, $blnProtected = false ) {

                $objDCACallback = new DCACallbacks();
                $strTemplate = $this->IconGetter->setTreeViewIcon( $this->arrCatalog['tablename'], $arrRow, $strLabel, $dc, $strImageAttribute, $blnReturnImage, $blnProtected );

                if ( $this->arrCatalog['useOwnLabelFormat'] ) {

                    $strTemplate .= !Toolkit::isEmpty( $this->arrCatalog['labelFormat'] ) ? $this->arrCatalog['labelFormat'] : $strTemplate;
                }

                else {

                    if ( !$arrRow['pid'] ) {

                        $strTemplate .= ' <strong>' . $strLabel . '</strong>';
                    }

                    else {

                        $strTemplate .= ' <span>' . $strLabel . '</span>';
                    }
                }

                return $objDCACallback->labelCallback( $strTemplate, $this->arrFields, $arrRow, $strLabel );
            };
        }

        return $arrReturn;
    }


    protected function createSortingDataArray() {

        $arrHeaderFields = $this->arrCatalog['headerFields'];
        $arrSortingFields = $this->arrCatalog['sortingFields'];
        $strPanelLayout = implode( ',', $this->arrCatalog['panelLayout'] );

        if ( empty( $arrSortingFields ) ) {

            $arrSortingFields = [ 'title' ];
        }

        if ( empty( $arrHeaderFields ) ) {

            $arrHeaderFields = [ 'id', 'title', 'alias' ];
        }

        if ( strpos( $strPanelLayout, 'filter' ) !== false ) {

            $strPanelLayout = preg_replace( '/,/' , ';', $strPanelLayout, 1);
        }

        if ( empty( $this->arrCatalog['labelFields'] ) || !is_array( $this->arrCatalog['labelFields'] ) ) {

            $this->arrCatalog['labelFields'] = [ 'title' ];
        }

        $arrReturn = [

            'fields' => $arrSortingFields,
            'panelLayout' => $strPanelLayout,
            'headerFields' => $arrHeaderFields,
            'mode' => $this->arrCatalog['mode'],
            'flag' => $this->arrCatalog['flag'],
        ];

        $arrReturn['child_record_callback'] = function ( $arrRow ) {

            $strLabel = $this->arrCatalog['labelFields'][0];
            $strTemplate = '##' . $strLabel . '##';
            $objDCACallback = new DCACallbacks();

            if ( $this->arrCatalog['useOwnLabelFormat'] ) {

                $strTemplate = !Toolkit::isEmpty( $this->arrCatalog['labelFormat'] ) ? $this->arrCatalog['labelFormat'] : $strTemplate;
            }

            return $objDCACallback->childRecordCallback( $strTemplate, $this->arrFields, $arrRow, $strLabel );
        };

        if ( $this->arrCatalog['mode'] === '5' ) {

            unset( $arrReturn['flag'] );
            unset( $arrReturn['headerFields'] );

            $arrReturn['icon'] = $this->IconGetter->setCatalogIcon( $this->arrCatalog['tablename'] );
        }

        return $arrReturn;
    }


    protected function createOperationDataArray() {

        $arrReturn = [

            'edit' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['edit'],
                'href' => 'act=edit',
                'icon' => 'header.gif'
            ],

            'copy' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif'
            ],

            'cut' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ],

            'delete' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $this->I18nCatalogTranslator->getDeleteConfirmLabel() . '\'))return false;Backend.getScrollOffset()"'
            ],

            'toggle' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['toggle'],
                'icon' => 'visible.gif',
                'href' => sprintf( 'catalogTable=%s', $this->strTable ),
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility( this,%s,'. sprintf( "'%s'", $this->strTable ) .' )"',
                'button_callback' => [ 'DCACallbacks',  'toggleIcon' ]
            ],

            'show' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            ]
        ];

        if ( in_array( $this->arrCatalog['mode'], [ '4', '5' ] ) ) {

            $arrReturn['copy']['href'] = 'act=paste&amp;mode=copy';
        }

        else {
            
            unset( $arrReturn['cut'] );
        }

        foreach ( $this->arrOperations as $strOperation => $blnActive ) {

            if ( $strOperation == 'invisible' && !$blnActive ) {

                unset( $arrReturn[ 'toggle' ] );

                continue;
            }

            if ( !$blnActive && isset( $arrReturn[ $strOperation ] ) ) {

                unset( $arrReturn[ $strOperation ] );
            }
        }

        foreach ( $this->arrCatalog['cTables'] as $arrCTable ) {

            if ( in_array( $arrCTable, $this->arrErrorTables ) ) continue;

            $arrChildTable = [];
            $strOperationName = sprintf( 'go_to_%s', $arrCTable );

            $arrChildTable[ $strOperationName ] = [

                'href' => sprintf( 'table=%s', $arrCTable ),
                'label' => [ sprintf( $GLOBALS['TL_LANG']['catalog_manager']['operations']['goTo'][0], $arrCTable ), sprintf( $GLOBALS['TL_LANG']['catalog_manager']['operations']['goTo'][1], $arrCTable ) ],
                'icon' => $arrCTable !== 'tl_content' ?  $this->IconGetter->setCatalogIcon( $arrCTable ) : 'articles.gif'
            ];

            array_insert( $arrReturn, 1, $arrChildTable );
        }

        if ( !empty( $this->arrFields ) && is_array( $this->arrFields ) ) {

            foreach ( $this->arrFields as $arrField ) {

                if ( $arrField['multiple'] ) continue;

                if ( !$arrField['fieldname'] || !$arrField['type'] == 'checkbox' ) continue;

                if ( $arrField['enableToggleIcon'] ) {

                    $arrToggleIcon = [];
                    $strVisibleIcon = $this->IconGetter->setToggleIcon( $arrField['fieldname'], true );
                    $strInVisibleIcon = $this->IconGetter->setToggleIcon( $arrField['fieldname'], false );
                    $strHref = sprintf( 'catalogTable=%s&fieldname=%s&iconVisible=%s', $this->strTable, $arrField['fieldname'], $strVisibleIcon );

                    $arrToggleIcon[ $arrField['fieldname'] ] = [

                        'href' => $strHref,
                        'icon' => $strInVisibleIcon,
                        'button_callback' => [ 'DCACallbacks',  'toggleIcon' ],
                        'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['toggleIcon'],
                        'attributes' => 'onclick="Backend.getScrollOffset();return CatalogManager.CatalogToggleVisibility( this,%s,'. sprintf( "'%s'", $strVisibleIcon ) .', '. sprintf( "'%s'", $strInVisibleIcon ) .', '. sprintf( "'%s'", $strHref ) .' )"'
                    ];

                    array_insert( $arrReturn, count( $arrReturn ), $arrToggleIcon );
                }
            }
        }

        return $arrReturn;
    }


    protected function createGlobalOperationDataArray() {

        return [

            'all' => [

                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ];
    }


    protected function createPaletteDataArray() {

        $strPalette = '';
        $strTemporaryPalette = 'general_legend';
        $arrPaletteTranslationMap = [ 'general_legend' => '', 'invisible_legend' => '' ];
        $arrDCAPalette = [ 'general_legend' => [ 'title', 'alias' ] ];

        foreach ( $this->arrFields as $arrField ) {

            if ( !$arrField['type'] ) continue;

            if ( $arrField['title'] && $arrField['type'] == 'fieldsetStart' ) {

                $strTemporaryPalette = $arrField['title'];
                $arrPaletteTranslationMap[ $arrField['title'] ] = $arrField['label'];
            }

            if ( !$arrField['fieldname'] ) {

                continue;
            }

            if ( in_array( $arrField['type'], $this->DCABuilderHelper->arrColumnsOnly ) ) {
                
               continue;
            }

            if ( in_array( $arrField['type'], $this->DCABuilderHelper->arrForbiddenInputTypes ) && !in_array( $arrField['type'], $this->DCABuilderHelper->arrReadOnlyInputTypes ) ) {

                continue;
            }
            
            $arrDCAPalette[ $strTemporaryPalette ][] = $arrField['fieldname'];
        }

        if ( $this->arrOperations['invisible'] ) {

            $arrDCAPalette['invisible_legend'] = [ 'invisible', 'start', 'stop' ];
        }

        $arrPalettes = array_keys( $arrDCAPalette );

        foreach ( $arrPalettes as $strPaletteTitle ) {

            $strPalette .= sprintf( '{%s},%s;', $this->I18nCatalogTranslator->getLegendLabel( $strPaletteTitle, $arrPaletteTranslationMap[ $strPaletteTitle ] ), implode( ',', $arrDCAPalette[ $strPaletteTitle ] ) );
        }
        
        return [ 'default' => $strPalette ];
    }


    protected function shouldBeUsedParentTable() {

        if ( !$this->arrCatalog['pTable'] ) {

            return false;
        }

        if ( $this->arrCatalog['isBackendModule'] ) {

            return false;
        }

        if ( !in_array( $this->arrCatalog['mode'], [ '4', '5' ] ) ) {

            return false;
        }

        return true;
    }
}