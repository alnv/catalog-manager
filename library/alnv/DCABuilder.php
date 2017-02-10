<?php

namespace CatalogManager;

class DCABuilder extends CatalogController {


    private $strID;
    private $strTable;
    private $arrFields = [];
    private $arrCatalog = [];
    private $arrErrorTables = [];


    private $arrOperations = [

        'cut' => false,
        'copy' => false,
        'invisible' => false
    ];


    public function __construct( $arrCatalog ) {

        parent::__construct();

        $this->import( 'Database' );
        $this->import( 'DCABuilderHelper' );
        $this->import( 'I18nCatalogTranslator' );

        $this->arrCatalog = $arrCatalog;
        $this->strID = $arrCatalog['id'];
        $this->strTable = $arrCatalog['tablename'];

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

                        $intPosition = array_search( $strTable , $this->arrCatalog['cTables'] );

                        unset( $this->arrCatalog['cTables'][ $intPosition ] );
                    }
                }
            }
        }
    }


    public function initializeI18n() {

        $this->I18nCatalogTranslator->initialize();
    }


    private function determineOperations() {

        $arrOperations = [];

        if ( $this->arrCatalog['operations'] ) {

            $arrOperations = deserialize( $this->arrCatalog['operations'] );
        }

        if ( !empty( $arrOperations ) && is_array($arrOperations) ) {

            foreach ( $arrOperations as $strOperation ) {

                $this->arrOperations[ $strOperation ] = isset( $this->arrOperations[ $strOperation ] );
            }
        }
    }


    private function getDCAFields() {

        $objCatalogFieldsDb = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE `pid` = ? AND invisible != ? ORDER BY `sorting`' )->execute( $this->strID, "1" );

        while ( $objCatalogFieldsDb->next() ) {

            $this->arrFields[] = $objCatalogFieldsDb->row();
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
    }


    private function parseDCAFields() {

        $arrDCAFields = $this->getDefaultDCFields();

        if ( !empty( $this->arrFields ) && is_array( $this->arrFields ) ) {

            $arrDCAFields = $this->DCABuilderHelper->convertCatalogFields2DCA( $this->arrFields, $arrDCAFields, $this->arrCatalog );
        }

        return $arrDCAFields;
    }


    private function getDefaultDCFields() {

        $arrReturn = $this->DCABuilderHelper->getPredefinedDCFields();

        $arrReturn['save_callback'] = [ function( $varValue, \DataContainer $dc ) {

            $objDCACallbacks = new DCACallbacks();

            return $objDCACallbacks->generateAlias( $varValue, $dc, 'title', $this->strTable );
        }];

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

        return $arrReturn;
    }


    private function createConfigDataArray() {

        $arrReturn = [

            'dataContainer' => 'Table',

            'onload_callback' => [],
            'onsubmit_callback' => [],

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

        if ( $this->arrCatalog['pTable'] ) {

            $arrReturn['onload_callback'][] = function() {

                $objDCAPermission = new DCAPermission();
                $objDCAPermission->checkPermissionByParent( $this->strTable, $this->arrCatalog['pTable'], $this->arrCatalog['pTable'], $this->arrCatalog['pTable'] . 'p' );
            };
        }

        else {

            $arrReturn['onload_callback'][] = function(){

                $objDCAPermission = new DCAPermission();
                $objDCAPermission->checkPermission( $this->strTable , $this->strTable, $this->strTable . 'p' );
            };
        }

        return $arrReturn;
    }


    private function createLabelDataArray() {

        $arrReturn = [

            'showColumns' => $this->arrCatalog['showColumns'] ? true : false,
            'fields' => empty( $this->arrCatalog['fields'] ) ? [ 'title' ] : $this->arrCatalog['fields'],
        ];

        if ( $this->arrCatalog['format'] ) {

            $arrReturn['format'] = $this->arrCatalog['format'];
        }

        return $arrReturn;
    }


    private function createSortingDataArray() {

        $arrFields = $this->arrCatalog['fields'];
        $headerFields = $this->arrCatalog['headerFields'];
        $strPanelLayout = implode( ',', $this->arrCatalog['panelLayout'] );

        if ( $this->arrCatalog['mode'] == '4' && empty( $this->arrCatalog['fields'] ) ) {

            $arrFields = ['sorting'];
        }

        if ( empty( $this->arrCatalog['fields'] ) ) {

            $arrFields = [ 'title' ];
        }

        if ( empty( $headerFields ) ) {

            $headerFields = [ 'id', 'title', 'alias' ];
        }

        if ( strpos( $strPanelLayout, 'filter' ) !== false ) {

            $strPanelLayout = preg_replace( '/,/' , ';', $strPanelLayout, 1);
        }

        $arrReturn = [

            'fields' => $arrFields,
            'headerFields' => $headerFields,
            'panelLayout' => $strPanelLayout,
            'mode' => $this->arrCatalog['mode'],
            'flag' => $this->arrCatalog['flag'],
            'child_record_callback' => [ 'DCACallbacks', 'createRowView' ],
        ];

        if ( $this->arrCatalog['mode'] === '5' ) {

            unset( $arrReturn['flag'] );
            unset( $arrReturn['headerFields'] );
        }

        return $arrReturn;
    }


    private function createOperationDataArray() {

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
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],

            'toggle' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['toggle'],
                'icon' => 'visible.gif',
                'href' => sprintf( 'table=%s', $this->strTable ),
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s, '. sprintf( "'%s'", $this->strTable ) .' )"',
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

                'label' => [ sprintf( $GLOBALS['TL_LANG']['catalog_manager']['operations']['goTo'][0], $arrCTable ), sprintf( $GLOBALS['TL_LANG']['catalog_manager']['operations']['goTo'][1], $arrCTable ) ],
                'href' => sprintf( 'table=%s', $arrCTable ),
                'icon' => 'edit.gif'
            ];

            array_insert( $arrReturn, 1, $arrChildTable );
        }

        return $arrReturn;
    }


    private function createGlobalOperationDataArray() {

        return [

            'all' => array
            (
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ];
    }


    private function createPaletteDataArray() {

        $strPalette = '';
        $strTemporaryPalette = 'general_legend';

        $arrPaletteTranslationMap = [

            'general_legend' => '',
            'invisible_legend' => ''
        ];

        $arrDCAPalette = [ 'general_legend' => [ 'title', 'alias' ] ];


        foreach ( $this->arrFields as $arrField ) {

            if ( !$arrField['type'] ) continue;

            if ( $arrField['title'] && $arrField['type'] == 'fieldsetStart' ) {

                $strTemporaryPalette = $arrField['title'];
                $arrPaletteTranslationMap[ $arrField['title'] ] = $arrField['label'];
            }

            if ( !$arrField['fieldname'] || in_array( $arrField['type'], $this->DCABuilderHelper->arrForbiddenInputTypes ) ) continue;

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

    
    private function shouldBeUsedParentTable() {

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