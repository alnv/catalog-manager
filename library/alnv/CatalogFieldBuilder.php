<?php

namespace CatalogManager;

class CatalogFieldBuilder extends CatalogController {


    protected $strTable = '';
    protected $arrCatalog = [];
    protected $arrCatalogFields = [];


    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
        $this->import( 'I18nCatalogTranslator' );
    }


    public function initialize( $strTablename ) {

        $this->strTable = $strTablename;

        if ( TL_MODE == 'BE' && !Toolkit::isEmpty( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTablename ] ) ) {

            $this->arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTablename ];
        }

        else {

            $objCatalog = $this->Database->prepare( 'SELECT * FROM tl_catalog WHERE `tablename` = ?' )->limit(1)->execute( $strTablename, '1' );

            if ( $objCatalog !== null ) {

                if ( $objCatalog->numRows ) {

                    $this->arrCatalog = Toolkit::parseCatalog( $objCatalog->row() );
                }
            }
        }
    }


    public function getCatalog() {

        return $this->arrCatalog;
    }


    public function getCatalogFields( $blnDcFormat = true, $objModule = null, $blnExcludeDefaults = false, $blnVisible = true ) {

        $arrFields = [];
        $blnIsCoreTable = Toolkit::isCoreTable( $this->strTable );

        if ( $blnIsCoreTable ) {

            $blnDcFormat = true;
            $blnExcludeDefaults = true;
            $arrFields = $this->getCoreFields( $blnDcFormat );
        }

        $arrFields = $blnExcludeDefaults ? $arrFields : $this->getDefaultCatalogFields();
        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE `pid` = ( SELECT id FROM tl_catalog WHERE `tablename` = ? LIMIT 1 )' . ( $blnVisible ? ' AND invisible != "1" ' : '' ) . 'ORDER BY `sorting`' )->execute( $this->strTable );

        if ( $objCatalogFields !== null ) {

            if ( $objCatalogFields->numRows ) {

                while ( $objCatalogFields->next() ) {

                    $arrField = $objCatalogFields->row();

                    if ( $objCatalogFields->fieldname && in_array( $objCatalogFields->fieldname, Toolkit::customizeAbleFields() ) ) {

                        $arrOrigin = $arrFields[ $objCatalogFields->fieldname ];

                        if ( is_null( $arrOrigin ) ) continue;

                        unset( $arrFields[ $objCatalogFields->fieldname ] );
                    }

                    $strFieldname = $objCatalogFields->fieldname ? $objCatalogFields->fieldname : $objCatalogFields->id;
                    $arrFields[ $strFieldname ] = $arrField;
                }
            }
        }

        $this->arrCatalogFields = $this->parseFieldsForDcFormat( $arrFields, $blnDcFormat, $objModule, $blnIsCoreTable );

        return $this->arrCatalogFields;
    }


    public function getDcFormatOnly() {

        $arrReturn = [];

        foreach ( $this->arrCatalogFields as $strFieldname => $arrField ) {

            if ( !empty( $arrField['_dcFormat'] ) && is_array( $arrField['_dcFormat'] ) ) {

                $arrReturn[ $strFieldname ] = $arrField['_dcFormat'];
            }
        }

        return $arrReturn;
    }


    public function parseFieldsForDcFormat( $arrFields, $blnDcFormat, $objModule = null, $blnCoreTable = false ) {

        $arrReturn = [];

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !isset( $arrField[ '_dcFormat' ] ) ) $arrField[ '_dcFormat' ] = null;
            if ( $blnDcFormat && Toolkit::isDcConformField( $arrField ) && !$arrField[ '_dcFormat' ] ) $arrField[ '_dcFormat' ] = $this->setDcFormatAttributes( $arrField, $objModule );
            if ( $arrField == null ) continue;

            $arrReturn[ $strFieldname ] = $this->prepareDefaultFields( $arrField, $strFieldname );
        }
        
        return $arrReturn;
    }


    public function setDcFormatAttributes( $arrField, $objModule = null ) {

        $strCSSBackendClasses = Toolkit::deserializeAndImplode( $arrField['tl_class'], ' ' );

        if ( Toolkit::isEmpty( $strCSSBackendClasses ) ) $strCSSBackendClasses = 'clr';

        $arrDcField = [

            'label' => $this->I18nCatalogTranslator->get( 'field', $arrField['fieldname'], [ 'title' => $arrField['label'], 'description' => $arrField['description'] ] ),
            'inputType' => Toolkit::setDcConformInputType( $arrField['type'] ),

            'eval' => [

                'tl_class' => $strCSSBackendClasses,
                'unique' => Toolkit::getBooleanByValue( $arrField['isUnique'] ),
                'nospace' => Toolkit::getBooleanByValue( $arrField['nospace'] ),
                'mandatory' => Toolkit::getBooleanByValue( $arrField['mandatory'] ),
                'doNotCopy' => Toolkit::getBooleanByValue( $arrField['doNotCopy'] ),
                'allowHtml' => Toolkit::getBooleanByValue( $arrField['allowHtml'] ),
                'trailingSlash' => Toolkit::getBooleanByValue( $arrField['trailingSlash'] ),
                'doNotSaveEmpty' => Toolkit::getBooleanByValue( $arrField['doNotSaveEmpty'] ),
                'spaceToUnderscore' => Toolkit::getBooleanByValue( $arrField['spaceToUnderscore'] ),
            ],

            'sorting' => Toolkit::getBooleanByValue( $arrField['sort'] ),
            'search' => Toolkit::getBooleanByValue( $arrField['search'] ),
            'filter' => Toolkit::getBooleanByValue( $arrField['filter'] ),
            'exclude' => Toolkit::getBooleanByValue( $arrField['exclude'] ),
            'sql' => Toolkit::getSqlDataType( $arrField['statement'] ),
        ];

        if ( !Toolkit::isEmpty( $arrField['flag'] ) ) $arrDcField['flag'] = $arrField['flag'];

        $arrDcField['_cssID'] = Toolkit::deserialize( $arrField['cssID'] );
        $arrDcField['_placeholder'] = $arrField['placeholder'];
        $arrDcField['_disableFEE'] = $arrField['disableFEE'];
        $arrDcField['_fieldname'] = $arrField['fieldname'];
        $arrDcField['_palette'] = $arrField['_palette'];
        $arrDcField['_type'] = $arrField['type'];

        if ( Toolkit::isDefined( $arrField['value'] ) && is_string( $arrField['value'] ) ) {

            $arrDcField['default'] = $arrField['value'];
        }

        if ( Toolkit::isDefined( $arrField['useIndex'] ) ) {

            $arrDcField['eval']['doNotCopy'] = true;

            if ( $arrField['useIndex'] == 'unique' ) $arrDcField['eval']['unique'] = true;
        }

        switch ( $arrField['type'] ) {

            case 'text':

                $arrDcField = Text::generate( $arrDcField, $arrField );

                break;

            case 'date':

                $arrDcField = DateInput::generate( $arrDcField, $arrField );

                break;

            case 'hidden':

                $arrDcField = Hidden::generate( $arrDcField, $arrField );

                break;

            case 'number':

                $arrDcField = Number::generate( $arrDcField, $arrField );

                break;

            case 'textarea':

                $arrDcField = Textarea::generate( $arrDcField, $arrField );

                break;

            case 'select':

                $arrDcField = Select::generate( $arrDcField, $arrField, $objModule );

                break;

            case 'radio':

                $arrDcField = Radio::generate( $arrDcField, $arrField, $objModule );

                break;

            case 'checkbox':

                $arrDcField = Checkbox::generate( $arrDcField, $arrField, $objModule );

                break;

            case 'upload':

                $arrDcField = Upload::generate( $arrDcField, $arrField );

                break;

            case 'message':

                $arrDcField = MessageInput::generate( $arrDcField, $arrField );

                break;

            case 'dbColumn':

                $arrDcField = DbColumn::generate( $arrDcField, $arrField );
                
                break;
        }

        return $arrDcField;
    }


    public function shouldBeUsedParentTable() {

        if ( !$this->arrCatalog['pTable'] ) {

            return false;
        }

        if ( $this->arrCatalog['isBackendModule'] ) {

            return false;
        }

        if ( !in_array( $this->arrCatalog['mode'], Toolkit::$arrRequireSortingModes ) ) {

            return false;
        }

        return true;
    }


    protected function getDefaultCatalogFields() {

        $arrFields = [

            'id' => [

                'type' => '',
                'sort' => '1',
                'search' => true,
                'invisible' => '',
                'fieldname' => 'id',
                'statement' => 'i10',
                'disableFEE' => true,
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['id'][0]
            ],

            'tstamp' => [

                'flag' => 6,
                'type' => '',
                'sort' => '1',
                'invisible' => '',
                '_isDate' => true,
                'statement' => 'i10',
                'disableFEE' => true,
                'fieldname' => 'tstamp',
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['tstamp'][0],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['tstamp'][0]
            ],

            'pid' => [

                'type' => '',
                'invisible' => '',
                'disableFEE' => true,
                'statement' => 'i10',
                'fieldname' => 'pid',
                'placeholder' =>  &$GLOBALS['TL_LANG']['catalog_manager']['fields']['pid'][0]
            ],

            'sorting' => [

                'type' => '',
                'invisible' => '',
                'statement' => 'i10',
                'disableFEE' => true,
                'fieldname' => 'sorting',
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['sorting'][0]
            ],

            'title' => [

                'sort' => '1',
                'search' => '1',
                'type' => 'text',
                'exclude' => '1',
                'invisible' => '',
                'maxlength' => '128',
                'statement' => 'c128',
                'fieldname' => 'title',
                '_palette' => 'general_legend',
                'tl_class' => serialize( [ 'w50' ] ),
                'cssID' => serialize( [ '', 'title' ] ),
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['title'][0],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['title'][0]
            ],

            'alias' => [

                'search' => '1',
                'unique' => '1',
                'type' => 'text',
                'exclude' => '1',
                'rgxp' => 'alias',
                'invisible' => '',
                'doNotCopy' => '1',
                'maxlength' => '128',
                'statement' => 'c128',
                'fieldname' => 'alias',
                '_palette' => 'general_legend',
                'tl_class' => serialize( [ 'w50' ] ),
                'cssID' => serialize( [ '', 'alias' ] ),
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['alias'][0],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['alias'][0]
            ],

            'invisible' => [

                'exclude' => '1',
                'invisible' => '',
                'statement' => 'c1',
                'placeholder' => '',
                'type' => 'checkbox',
                'fieldname' => 'invisible',
                '_palette' => 'invisible_legend',
                'cssID' => serialize( [ '', 'invisible' ] ),
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['invisible'][0],
            ],

            'start' => [

                'flag' => 6,
                'sort' => '1',
                'type' => 'date',
                'exclude' => '1',
                'rgxp' => 'datim',
                'invisible' => '',
                'datepicker' => '1',
                'statement' => 'c16',
                'fieldname' => 'start',
                '_palette' => 'invisible_legend',
                'cssID' => serialize( [ '', 'start' ] ),
                'tl_class' =>  serialize( [ 'w50 wizard' ] ),
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['start'][0],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['start'][0]
            ],

            'stop' => [

                'flag' => 6,
                'sort' => '1',
                'type' => 'date',
                'exclude' => '1',
                'rgxp' => 'datim',
                'invisible' => '',
                'datepicker' => '1',
                'statement' => 'c16',
                'fieldname' => 'stop',
                '_palette' => 'invisible_legend',
                'cssID' => serialize( [ '', 'stop' ] ),
                'tl_class' =>  serialize( [ 'w50 wizard' ] ),
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['stop'][0],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['stop'][0]
            ]
        ];

        if ( !$this->arrCatalog['pTable'] && !in_array( $this->arrCatalog['mode'], Toolkit::$arrRequireSortingModes ) ) {

            unset( $arrFields['pid'] );
        }

        if ( is_array( $this->arrCatalog['operations'] ) ) {

            if ( !in_array( 'invisible', $this->arrCatalog['operations'] ) ) {

                unset( $arrFields['stop'] );
                unset( $arrFields['start'] );
                unset( $arrFields['invisible'] );;
            }
        }

        if ( !in_array( $this->arrCatalog['mode'], Toolkit::$arrRequireSortingModes ) ) {

            unset( $arrFields['sorting'] );
        }

        return $arrFields;
    }


    protected function prepareDefaultFields( $arrField, $strFieldname ) {

        switch ( $strFieldname ) {

            case 'tstamp' :
            case 'id' :

                $arrField['_dcFormat'] = [

                    'sorting' => $arrField['_dcFormat']['sorting'],
                    'label' => $arrField['_dcFormat']['label'],
                    'flag' => $arrField['_dcFormat']['flag'],
                    'sql' => $arrField['_dcFormat']['sql']
                ];

                return $arrField;

                break;

            case 'pid' :

                if ( $this->arrCatalog['pTable'] ) {

                    $arrField['_dcFormat'] = [

                        'label' => $arrField['_dcFormat']['label'],
                        'sql' => "int(10) unsigned NOT NULL default '0'",
                        'foreignKey' => sprintf( '%s.id', $this->arrCatalog['pTable'] ),

                        'relation' => [

                            'type' => 'belongsTo',
                            'load' => 'eager'
                        ]
                    ];

                    return $arrField;
                }

                break;

            case 'sorting' :

                if ( in_array( $this->arrCatalog['mode'], Toolkit::$arrRequireSortingModes ) ) {

                    $arrField['_dcFormat'] = [

                        'label' => $arrField['_dcFormat']['label'],
                        'sql' => "int(10) unsigned NOT NULL default '0'"
                    ];

                    return $arrField;
                }

                break;


            case 'alias':

                if ( TL_MODE == 'FE' ) return $arrField;

                $arrField['_dcFormat']['save_callback'] = [ function( $varValue, \DataContainer $dc ) {

                    $objDcCallbacks = new DcCallbacks();
                    return $objDcCallbacks->generateAlias( $varValue, $dc, 'title', $this->strTable );
                }];

                return $arrField;

                break;
        }

        return $arrField;
    }


    protected function getCoreFields( $blnDcFormat ) {

        $arrReturn = [];

        \Controller::loadLanguageFile( $this->strTable );
        \Controller::loadDataContainer( $this->strTable );

        $arrFields = $GLOBALS['TL_DCA'][ $this->strTable ]['fields'];

        if ( !empty( $arrFields ) && is_array( $arrFields ) ) {

            foreach ( $arrFields as $strFieldname => $arrField ) {

                $strType = Toolkit::setCatalogConformInputType( $arrField );

                $arrField['_type'] = $strType;
                $arrField['_disableFEE'] = '';
                $arrField['_placeholder'] = '';
                $arrField['_fieldname'] = $strFieldname;
                $arrField['_palette'] = 'general_legend';
                $arrField['_cssID'] = [ '', $strFieldname ];
                
                if ( TL_MODE == 'FE' ) {

                    unset( $arrField['options_callback'] );
                    unset( $arrField['save_callback'] );
                    unset( $arrField['load_callback'] );
                }

                $arrReturn[ $strFieldname ] = [

                    '_core' => true,
                    'type' => $strType,
                    'fieldname' => $strFieldname,
                    '_palette' => 'general_legend',
                    'title' => $arrField['label'][0] ?: '',
                    'rgxp' => $arrField['eval']['rgxp'] ?: '',
                    'description' => $arrField['label'][1] ?: '',
                    'exclude' => $arrField['exclude'] ? '1' : '',
                    'cssID' => serialize( [ '', $strFieldname ] ),
                    '_dcFormat' => $blnDcFormat ? $arrField : null,
                    'tl_class' =>  $arrField['eval']['tl_class'] ?: '',
                    'multiple' => $arrField['eval']['multiple'] ? '1': '',
                    'datepicker' => $arrField['eval']['datepicker'] ? '1': ''
                ];

                if ( $strType == 'upload' ) {

                    $arrReturn[ $strFieldname ]['fileType'] = $arrField['_fileType'] ?: '';
                    $arrReturn[ $strFieldname ]['extensions'] = $arrField['eval']['extensions'] ?: '';
                    $arrReturn[ $strFieldname ]['filesOnly'] = $arrField['eval']['filesOnly'] ? '1' : '';
                }
            }
        }

        return $arrReturn;
    }
}