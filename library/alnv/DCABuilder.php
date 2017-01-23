<?php

namespace CatalogManager;

class DCABuilder extends CatalogSystem {

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

        $this->import( 'Database' );
        $this->import( 'i18nCatalogTranslator' );

        $this->arrCatalog = $arrCatalog;

        $this->strID = $arrCatalog['id'];

        $this->strTable = $arrCatalog['tablename'];

        if ( !$this->strTable ) return null;

        if ( \Input::get( 'do' ) && \Input::get( 'do' ) == $this->arrCatalog['name'] ) {

            $objReviseRelatedTables = new ReviseRelatedTables();

            if ( $objReviseRelatedTables->reviseCatalogTables( $this->arrCatalog['tablename'] , $this->arrCatalog['pTable'], $this->arrCatalog['cTables'] ) ) {

                foreach ( $objReviseRelatedTables->getErrorTables() as $strTable ) {

                    \Message::addError( sprintf( "This table '%s' can not be used as relation. Please delete all rows or create valid pid value.", $strTable ) );

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

        $this->i18nCatalogTranslator->initialize();
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

        $arrDCAFields = $this->getDefaultDCAFields();

        if ( !empty( $this->arrFields ) && is_array( $this->arrFields ) ) {

            foreach ( $this->arrFields as $arrField ) {

                if ( empty( $arrField ) && !is_array( $arrField ) ) continue;

                if ( !$arrField['type'] ) continue;

                if ( in_array( $arrField['type'], DCAHelper::$arrForbiddenInputTypes ) ) continue;

                $arrDCAField = [

                    'label' => $this->i18nCatalogTranslator->getFieldLabel( $arrField['fieldname'], $arrField['label'], $arrField['description'] ),
                    'inputType' => DCAHelper::setInputType( $arrField ),

                    'eval' => [

                        'unique' => Toolkit::getBooleanByValue( $arrField['unique'] ),
                        'nospace' => Toolkit::getBooleanByValue( $arrField['nospace'] ),
                        'mandatory' => Toolkit::getBooleanByValue( $arrField['mandatory'] ),
                        'doNotCopy' => Toolkit::getBooleanByValue( $arrField['doNotCopy'] ),
                        'allowHtml' => Toolkit::getBooleanByValue( $arrField['allowHtml'] ),
                        'tl_class' => Toolkit::deserializeAndImplode( $arrField['tl_class'], ' ' ),
                        'trailingSlash' => Toolkit::getBooleanByValue( $arrField['trailingSlash'] ),
                        'doNotSaveEmpty' => Toolkit::getBooleanByValue( $arrField['doNotSaveEmpty'] ),
                        'spaceToUnderscore' => Toolkit::getBooleanByValue( $arrField['spaceToUnderscore'] ),
                    ],

                    'sorting' => Toolkit::getBooleanByValue( $arrField['sort'] ),
                    'search' => Toolkit::getBooleanByValue( $arrField['search'] ),
                    'filter' => Toolkit::getBooleanByValue( $arrField['filter'] ),
                    'exclude' => Toolkit::getBooleanByValue( $arrField['exclude'] ),
                    'sql' => DCAHelper::$arrSQLStatements[ $arrField['statement'] ]
                ];

                if ( $arrField['flag'] ) {

                    $arrDCAField['default'] = $arrField['flag'];
                }

                if ( Toolkit::isDefined( $arrField['value'] ) && is_string( $arrField['value'] ) ) {

                    $arrDCAField['default'] = $arrField['value'];
                }

                if ( Toolkit::isDefined( $arrField['useIndex'] ) ) {

                    $arrDCAField['eval']['doNotCopy'] = true;

                    if ( $arrField['useIndex'] == 'unique' ) {

                        $arrDCAField['eval']['unique'] = true;
                    }
                }

                switch ( $arrField['type'] ) {

                    case 'text':

                        $arrDCAField = Text::generate( $arrDCAField, $arrField );

                        break;

                    case 'date':

                        $arrDCAField = DateInput::generate( $arrDCAField, $arrField );

                        break;

                    case 'hidden':

                        $arrDCAField = Hidden::generate( $arrDCAField, $arrField );

                        break;

                    case 'number':

                        $arrDCAField = Number::generate( $arrDCAField, $arrField );

                        break;

                    case 'textarea':

                        $arrDCAField = Textarea::generate( $arrDCAField, $arrField );

                        break;

                    case 'select':

                        $arrDCAField = Select::generate( $arrDCAField, $arrField );

                        break;

                    case 'radio':

                        $arrDCAField = Radio::generate( $arrDCAField, $arrField );

                        break;

                    case 'checkbox':

                        $arrDCAField = Checkbox::generate( $arrDCAField, $arrField );

                        break;

                    case 'upload':

                        $arrDCAField = Upload::generate( $arrDCAField, $arrField );

                        break;
                }

                $arrDCAFields[ $arrField['fieldname'] ] = $arrDCAField;
            }
        }

        return $arrDCAFields;
    }

    private function getDefaultDCAFields() {

        $arrReturn = [

            'id' => [

                'sql' => "int(10) unsigned NOT NULL auto_increment"
            ],

            'tstamp' => [

                'sql' => "int(10) unsigned NOT NULL default '0'"
            ],

            'title' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['title'],
                'inputType' => 'text',

                'eval' => [

                    'unique' => true,
                    'rgxp' => 'alias',
                    'maxlength' => 128,
                    'tl_class' => 'w50',
                ],

                'exclude' => true,
                'sql' => "varchar(128) NOT NULL default ''"
            ],

            'alias' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['alias'],
                'inputType' => 'text',

                'eval' => [

                    'maxlength' => 128,
                    'tl_class' => 'w50',
                    'doNotCopy' => true,
                ],

                'save_callback' => [ [ 'DCACallbacks', 'generateAlias' ] ],

                'exclude' => true,
                'sql' => "varchar(128) NOT NULL default ''"
            ],

            'invisible' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['invisible'],
                'inputType' => 'checkbox',
                'sql' => "char(1) NOT NULL default ''"
            ]
        ];

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

            'sql' => [

                'keys' => [

                    'id' => 'primary'
                ]
            ]
        ];

        foreach ( $this->arrFields as $arrField ) {

            if ( !$arrField['useIndex'] ) continue;

            $arrReturn['sql']['keys'][ $arrField['fieldname'] ] = $arrField['useIndex'];
        }

        if ( $this->shouldBeUsedParentTable() ) {

            $arrReturn['ptable'] = $this->arrCatalog['pTable'];
        }

        if ( !empty( $this->arrCatalog['cTables'] ) && is_array( $this->arrCatalog['cTables'] ) ) {

            $arrReturn['ctable'] = $this->arrCatalog['cTables'];
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
        $strLegendPointer = 'general_legend';

        $arrDCAPalette = [

            'general_legend' => [ 'title', 'alias' ]
        ];

        foreach ( $this->arrFields as $arrField ) {

            if ( !$arrField['type'] ) continue;

            if ( $arrField['title'] && $arrField['type'] == 'fieldsetStart' ) {

                $strLegendPointer = $arrField['title'];
            }

            if ( !$arrField['fieldname'] || in_array( $arrField['type'], DCAHelper::$arrForbiddenInputTypes ) ) continue;

            $arrDCAPalette[ $strLegendPointer ][] = $arrField['fieldname'];
        }

        if ( $this->arrOperations['invisible'] ) {

            $arrDCAPalette['invisible_legend'] = [ 'invisible' ];
        }

        $arrLegends = array_keys( $arrDCAPalette );

        foreach ( $arrLegends as $strLegend ) {

            $strI18nLegend = $this->i18nCatalogTranslator->getLegendLabel( $strLegend );
            
            $strPalette .= sprintf( '{%s},%s;', $strI18nLegend, implode( ',', $arrDCAPalette[ $strLegend ] ) );
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