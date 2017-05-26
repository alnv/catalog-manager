<?php

namespace CatalogManager;

class FrontendEditing extends CatalogController {


    public $strAct;
    public $strItemID;
    public $strPageID;
    public $strRedirectID;
    public $arrOptions = [];
    public $strTemplate = '';

    protected $objTemplate;
    protected $arrValues = [];
    protected $arrCatalog = [];
    protected $arrPalettes = [];
    protected $arrFormFields = [];
    protected $strSubmitName = '';
    protected $blnNoSubmit = false;
    protected $blnHasUpload = false;
    protected $arrPaletteNames = [];
    // protected $blnTinyMCEScript = false;
    protected $strTemporaryPalette = 'general_legend';


    public function __construct() {

        parent::__construct();

        $this->import( 'CatalogEvents' );
        $this->import( 'SQLQueryHelper' );
        $this->import( 'SQLQueryBuilder' );
        $this->import( 'DCABuilderHelper' );
        $this->import( 'I18nCatalogTranslator' );
    }


    public function initialize() {

        global $objPage;

        \System::loadLanguageFile('catalog_manager');

        $this->setOptions();

        if ( $this->strItemID && $this->strAct && in_array( $this->strAct, [ 'copy', 'edit' ] ) ) {

            $this->setValues();
        }

        $this->strSubmitName = 'catalog_' . $this->catalogTablename;
        $this->arrCatalog = $this->SQLQueryHelper->getCatalogByTablename( $this->catalogTablename );
        $arrFormFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogID( $this->arrCatalog['id'], function ( $arrField, $strFieldname ) {

            if ( $arrField['type'] == 'fieldsetStart' ) {

                $this->strTemporaryPalette = $arrField['title'];
                $this->arrPaletteNames[ $this->strTemporaryPalette ] = $this->I18nCatalogTranslator->getLegendLabel( $arrField['title'], $arrField['label'] );
            }

            if ( !$this->DCABuilderHelper->isValidField( $arrField ) ) return null;

            $arrDCField = $this->DCABuilderHelper->convertCatalogField2DCA( $arrField );

            if ( $arrField['type'] == 'hidden' ) {

                $arrDCField['inputType'] = 'hidden';
            }

            $arrDCField['_fieldname'] = $strFieldname;
            $arrDCField['_palette'] = $this->strTemporaryPalette;

            return $arrDCField;
        });

        if ( !empty( $arrFormFields ) && is_array( $arrFormFields ) ) {

            foreach ( $arrFormFields as $arrFormField ) {

                if ( !$arrFormField ) continue;

                $this->arrFormFields[ $arrFormField['_fieldname'] ] = $arrFormField;
            }
        }

        $this->catalogExcludedFields = Toolkit::deserialize( $this->catalogExcludedFields );
        $this->arrCatalog['operations'] = Toolkit::deserialize( $this->arrCatalog['operations'] );

        $arrPredefinedDCFields = $this->DCABuilderHelper->getPredefinedDCFields();

        if ( in_array( 'invisible', $this->arrCatalog['operations'] ) ) {

            $this->arrFormFields[ 'invisible' ] = $arrPredefinedDCFields['invisible'];
        }

        unset( $arrPredefinedDCFields['stop'] );
        unset( $arrPredefinedDCFields['start'] );
        unset( $arrPredefinedDCFields['invisible'] );

        array_insert( $this->arrFormFields, 0, $arrPredefinedDCFields );

        if ( $this->catalogFormRedirect && $this->catalogFormRedirect !== '0' ) {

            $this->strRedirectID = $this->catalogFormRedirect;
        }

        else {

            $this->strRedirectID = $objPage->id;
        }

        $this->strPageID = $objPage->id;
    }


    public function isVisible() {

        if ( !\Input::get( 'auto_item' ) || !$this->catalogTablename ) {

            return false;
        }

        $arrQuery = [

            'table' => $this->catalogTablename,

            'where' => [

                [
                    [
                        'field' => 'id',
                        'operator' => 'equal',
                        'value' => \Input::get( 'auto_item' )
                    ],

                    [
                        'field' => 'alias',
                        'operator' => 'equal',
                        'value' => \Input::get( 'auto_item' )
                    ]
                ]
            ]
        ];

        if ( is_array( $this->arrCatalog['operations'] ) && in_array( 'invisible', $this->arrCatalog['operations']  ) && !BE_USER_LOGGED_IN ) {

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

        $objEntities = $this->SQLQueryBuilder->execute( $arrQuery );
        
        return $objEntities->numRows ? true : false;
    }


    public function checkAccess() {

        $this->import( 'FrontendEditingPermission' );

        $this->FrontendEditingPermission->blnDisablePermissions = $this->catalogEnableFrontendPermission ? false : true;
        $this->FrontendEditingPermission->initialize();
        
        return $this->FrontendEditingPermission->hasAccess( $this->catalogTablename );
    }


    public function checkPermission( $strMode ) {

        $this->import( 'FrontendEditingPermission' );

        $this->FrontendEditingPermission->blnDisablePermissions = $this->catalogEnableFrontendPermission ? false : true;
        $this->FrontendEditingPermission->initialize();

        return $this->FrontendEditingPermission->hasPermission( $strMode, $this->catalogTablename );
    }
    
    
    public function getCatalogForm() {

        $this->objTemplate = new \FrontendTemplate( $this->strTemplate );
        $this->objTemplate->setData( $this->arrOptions );

        if ( !is_array( $this->catalogExcludedFields ) ) {

            $this->catalogExcludedFields = [];
        }

        if ( !empty( $this->arrFormFields ) && is_array( $this->arrFormFields ) ) {

            foreach ( $this->arrFormFields as $arrField ) {

                if ( !$arrField ) continue;

                if ( in_array( $arrField['_fieldname'], $this->catalogExcludedFields ) ) continue;

                $this->generateForm( $arrField );
            }
        }

        if ( !$this->disableCaptcha ) {

            $objCaptcha = $this->getCaptcha();
            $this->objTemplate->captchaWidget = $objCaptcha->parse();
        }

        if ( !$this->blnNoSubmit && \Input::post('FORM_SUBMIT') == $this->strSubmitName ) {

            $this->saveEntity();
        }


        $arrCategories = [];
        $arrInvisiblePalette = [];

        foreach ( $this->arrPalettes as $strPalette => $arrPalette ) {

            if ( $strPalette === 'invisible_legend' ) {

                $arrInvisiblePalette[ $this->arrPaletteNames[ $strPalette ] ] = $arrPalette;
                continue;
            }

            $arrCategories[ $this->arrPaletteNames[ $strPalette ] ] = $arrPalette;
        }

        $this->objTemplate->method = 'POST';
        $this->objTemplate->categories = $arrCategories;
        $this->objTemplate->formId = $this->strSubmitName;
        $this->objTemplate->invisible = $arrInvisiblePalette;
        $this->objTemplate->submitName = $this->strSubmitName;
        $this->objTemplate->action = \Environment::get( 'indexFreeRequest' );
        $this->objTemplate->attributes = $this->catalogNoValidate ? 'novalidate' : '';
        $this->objTemplate->submit = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['submit'];
        $this->objTemplate->captchaLabel = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['captchaLabel'];
        $this->objTemplate->enctype = $this->blnHasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';

        return $this->objTemplate->parse();
    }


    protected function generateForm( $arrField ) {

        $arrField = $this->convertWidgetToField( $arrField );
        $strClass = $this->fieldClassExist( $arrField['inputType'] );

        if ( $strClass == false ) return null;

        if ( is_bool( $arrField['_disableFEE'] ) && $arrField['_disableFEE'] == true ) {

            return null;
        }

        $objWidget = new $strClass( $strClass::getAttributesFromDca( $arrField, $arrField['_fieldname'], $arrField['default'], '', '' ) );

        $objWidget->storeValues = true;
        $objWidget->id = 'id_' . $arrField['_fieldname'];
        $objWidget->value = $this->arrValues[ $arrField['_fieldname'] ];
        $objWidget->placeholder = $arrField['_placeholder'] ? $arrField['_placeholder'] : '';

        if ( is_array( $arrField['_cssID'] ) && ( $arrField['_cssID'][0] || $arrField['_cssID'][1] ) ) {

            if ( $arrField['_cssID'][0] ) {

                $objWidget->id = 'id_' . $arrField['_cssID'][0];
            }

            if ( $arrField['_cssID'][1] ) {

                $objWidget->class = ' ' . $arrField['_cssID'][1];
            }
        }

        if ( $this->strAct == 'copy' && $arrField['eval']['doNotCopy'] === true ) {

            $objWidget->value = '';
        }

        if ( $arrField['eval']['multiple'] && $arrField['eval']['csv'] && is_string( $objWidget->value ) ) {

            $objWidget->value = explode( $arrField['eval']['csv'], $objWidget->value );
        }

        if ( $arrField['inputType'] == 'upload' ) {

            $objWidget->storeFile = $this->catalogStoreFile;
            $objWidget->useHomeDir = $this->catalogUseHomeDir;
            $objWidget->uploadFolder = $this->catalogUploadFolder;
            $objWidget->doNotOverwrite = $this->catalogDoNotOverwrite;
            $objWidget->extensions = $arrField['eval']['extensions'];
            $objWidget->maxlength = $arrField['eval']['maxsize'];

            $this->blnHasUpload = true;
        }

        if ( $arrField['inputType'] == 'textarea' && isset( $arrField['eval']['rte'] ) ) {

            $objWidget->mandatory = false;

            $arrData = [

                'selector' => 'ctrl_' . $objWidget->id
            ];

            if ( version_compare( VERSION, '4.0', '>=' ) ) {

                $strTemplate = 'be_' . $arrField['eval']['rte'];
            }

            else {

                $strTemplate = 'ctlg_catalog_tinyMCE';
                $arrData['tinyMCE'] = TL_ROOT . '/' . 'system/config/' . $arrField['eval']['rte'] . '.php';
            }

            $objScript = new \FrontendTemplate( $strTemplate );
            $objScript->setData( $arrData );
            $strScript = $objScript->parse();

            $GLOBALS['TL_HEAD'][] = $strScript;
        }

        if ( !$objWidget->value && $arrField['default'] ) {

            $objWidget->value = $arrField['default'];
        }

        if ( $arrField['eval']['rgxp'] && in_array( $arrField['eval']['rgxp'], [ 'date', 'time', 'datim' ] ) ) {

            $strDateFormat = \Date::getFormatFromRgxp( $arrField['eval']['rgxp'] );
            $objWidget->value = $objWidget->value ? \Date::parse( $strDateFormat, $objWidget->value ) : '';
        }

        if ( \Input::post('FORM_SUBMIT') == $this->strSubmitName ) {

            $objWidget->validate();
            $varValue = $objWidget->value;

            if ( $varValue && is_string( $varValue ) ) {

                $varValue = $this->decodeValue( $varValue );
                $varValue = $this->replaceInsertTags( $varValue );
            }

            if ( $varValue != '' && in_array( $arrField, [ 'date', 'time', 'datim' ] ) ) {

                try {

                    $objDate = new \Date( $varValue, \Date::getFormatFromRgxp( $arrField['eval']['rgxp'] ) );
                    $varValue = $objDate->tstamp;

                } catch ( \OutOfBoundsException $objError ) {

                    $objWidget->addError( sprintf( $GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue ) );
                }
            }

            if ( $arrField['eval']['unique'] && $varValue != '' && !$this->SQLQueryHelper->SQLQueryBuilder->Database->isUniqueValue( $this->catalogTablename, $arrField['_fieldname'], $varValue, ( $this->strAct == 'edit' ? $this->strItemID : null ) ) ) {

                $objWidget->addError( sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $arrField['label'][0] ?: $arrField['_fieldname'] ) );
            }

            if ( $objWidget->submitInput() && !$objWidget->hasErrors() && is_array( $arrField['save_callback'] ) ) {

                foreach ( $arrField['save_callback'] as $arrCallback ) {

                    try {

                        if ( is_array( $arrCallback ) ) {

                            $this->import( $arrCallback[0] );
                            $varValue = $this->{$arrCallback[0]}->{$arrCallback[1]}( $varValue, null );
                        }

                        elseif( is_callable( $arrCallback ) ) {

                            $varValue = $arrCallback( $varValue, null );
                        }
                    }

                    catch ( \Exception $objError ) {

                        $objWidget->class = 'error';
                        $objWidget->addError( $objError->getMessage() );
                    }
                }
            }

            if ( $arrField['_fieldname'] == 'alias' ) {

                $objDCACallbacks = new DCACallbacks();
                $varValue = $objDCACallbacks->generateFEAlias( $varValue, $this->arrValues['title'], $this->catalogTablename, $this->arrValues['id'] );
            }

            if ( $objWidget->hasErrors() ) {

                $this->blnNoSubmit = true;
            }

            elseif( $objWidget->submitInput() ) {

                if ( $varValue === '' ) {

                    $varValue = $objWidget->getEmptyValue();
                }

                if ( $arrField['eval']['encrypt'] ) {

                    $varValue = \Encryption::encrypt( $varValue );
                }

                $this->arrValues[ $arrField['_fieldname'] ] = $varValue;
            }

            $arrFiles = $_SESSION['FILES'];

            if ( !empty( $arrFiles ) && isset( $arrFiles[ $arrField['_fieldname'] ] ) && $this->catalogStoreFile ) {

                $strRoot = TL_ROOT . '/';
                $strUuid = $arrFiles[ $arrField['_fieldname'] ]['uuid'];
                $strFile = substr( $arrFiles[ $arrField['_fieldname'] ]['tmp_name'], strlen( $strRoot ) );
                $objFiles = \FilesModel::findByPath( $strFile );

                if ( $objFiles !== null ) {

                    $strUuid = $objFiles->uuid;
                }

                $this->arrValues[ $arrField['_fieldname'] ] = $strUuid;
            }

            if ( !empty( $arrFiles ) && isset( $arrFiles[ $arrField['_fieldname'] ] ) ) {

                unset( $_SESSION['FILES'][ $arrField['_fieldname'] ] );
            }
        }

        $strWidget = $objWidget->parse();
        $this->arrPalettes[ $arrField['_palette'] ][ $arrField['_fieldname'] ] = $strWidget;

        if ( is_null( $this->arrPaletteNames[ $arrField['_palette'] ] ) ) {

            $this->arrPaletteNames[ $arrField['_palette'] ] = $this->I18nCatalogTranslator->getLegendLabel( $arrField['_palette'] );
        }
    }


    public function deleteEntity() {

        $this->import( 'SQLBuilder' );

        if (  $this->SQLBuilder->Database->tableExists( $this->catalogTablename ) ) {

            if ( $this->catalogNotifyDelete ) {

                $objCatalogNotification = new CatalogNotification( $this->catalogTablename, $this->strItemID );
                $objCatalogNotification->notifyOnDelete( $this->catalogNotifyDelete, [] );
            }

            $arrData = [

                'row' => [],
                'id' => $this->strItemID,
                'table' => $this->catalogTablename
            ];

            $this->CatalogEvents->addEventListener( 'delete', $arrData );
            $this->SQLBuilder->Database->prepare( sprintf( 'DELETE FROM %s WHERE id = ? ', $this->catalogTablename ) )->execute( $this->strItemID );
        }

        $this->redirectToFrontendPage( $this->strRedirectID );
    }


    protected function saveEntity() {

        $this->import( 'SQLBuilder' );

        if ( $this->arrCatalog['useGeoCoordinates'] ) {

            $arrCords = [];
            $objGeoCoding = new GeoCoding();
            $strGeoInputType = $this->arrCatalog['addressInputType'];

            switch ( $strGeoInputType ) {

                case 'useSingleField':

                    $arrCords = $objGeoCoding->getCords( $this->arrValues[ $this->arrCatalog['geoAddress'] ], 'en', true );

                    break;

                case 'useMultipleFields':

                    $objGeoCoding->setCity( $this->arrValues[ $this->arrCatalog['geoCity'] ] );
                    $objGeoCoding->setStreet( $this->arrValues[ $this->arrCatalog['geoCity'] ] );
                    $objGeoCoding->setPostal( $this->arrValues[ $this->arrCatalog['geoPostal'] ] );
                    $objGeoCoding->setCountry( $this->arrValues[ $this->arrCatalog['geoCountry'] ] );
                    $objGeoCoding->setStreetNumber( $this->arrValues[ $this->arrCatalog['geoStreetNumber'] ] );

                    $arrCords = $objGeoCoding->getCords( '', 'en', true );

                    break;
            }

            if ( ( $arrCords['lat'] || $arrCords['lng'] ) && ( $this->arrCatalog['lngField'] && $this->arrCatalog['latField'] ) ) {

                $this->arrValues[ $this->arrCatalog['lngField'] ] = $arrCords['lng'];
                $this->arrValues[ $this->arrCatalog['latField'] ] = $arrCords['lat'];
            }
        }

        $this->prepareData();

        $strQuery = '';

        if ( \Input::get('pid') ) {

            $strQuery = sprintf( '?pid=%s', \Input::get('pid') );
        }

        if ( !$this->arrValues['alias'] ) {

            $objDCACallbacks = new DCACallbacks();
            $this->arrValues['alias'] = $objDCACallbacks->generateFEAlias( '', $this->arrValues['title'], $this->catalogTablename, $this->arrValues['id'] );
        }

        switch ( $this->strAct ) {

            case 'create':

                if ( $this->SQLBuilder->Database->fieldExists( 'pid', $this->catalogTablename ) && $this->arrCatalog['pTable'] ) {

                   if ( !\Input::get('pid') ) return null;

                    $this->arrValues['pid'] = \Input::get('pid');
                }

                if ( $this->SQLBuilder->Database->fieldExists( 'sorting', $this->catalogTablename ) ) {

                    $intSort = $this->SQLBuilder->Database->prepare( sprintf( 'SELECT MAX(sorting) FROM %s;', $this->catalogTablename ) )->execute()->row( 'MAX(sorting)' )[0];
                    $this->arrValues['sorting'] = intval( $intSort ) + 100;
                }

                if ( $this->SQLBuilder->Database->fieldExists( 'tstamp', $this->catalogTablename ) ) {

                    $this->arrValues['tstamp'] = \Date::floorToMinute();
                }

                $this->SQLBuilder->Database->prepare( 'INSERT INTO '. $this->catalogTablename .' %s' )->set( $this->arrValues )->execute();

                if ( $this->catalogNotifyInsert ) {

                    $objCatalogNotification = new CatalogNotification( $this->catalogTablename );
                    $objCatalogNotification->notifyOnInsert( $this->catalogNotifyInsert, $this->arrValues );
                }

                $arrData = [

                    'id' => '',
                    'row' => $this->arrValues,
                    'table' => $this->catalogTablename,
                ];

                $this->CatalogEvents->addEventListener( 'create', $arrData );
                $this->redirectAfterInsertion( $this->strRedirectID, $strQuery );

                break;

            case 'edit':

                $blnReload = true;
                $objEntity = $this->SQLBuilder->Database->prepare( 'SELECT * FROM '. $this->catalogTablename .' WHERE id = ?' )->limit(1)->execute( $this->strItemID );

                if ( $objEntity->numRows ) {

                    if ( $this->arrValues['alias'] && $this->arrValues['alias'] !== $objEntity->alias ) {

                        $blnReload =  false;

                        if ( $objEntity->pid ) {

                            $strQuery = sprintf( '?pid=%s', $objEntity->pid );
                        }
                    }

                    if ( $this->catalogNotifyUpdate ) {

                        $objCatalogNotification = new CatalogNotification( $this->catalogTablename, $this->strItemID );
                        $objCatalogNotification->notifyOnUpdate( $this->catalogNotifyUpdate, $this->arrValues );
                    }

                    $arrData = [

                        'id' => $this->strItemID,
                        'row' => $this->arrValues,
                        'table' => $this->catalogTablename,
                    ];

                    $this->CatalogEvents->addEventListener( 'update', $arrData );
                    $this->SQLBuilder->Database->prepare( 'UPDATE '. $this->catalogTablename .' %s WHERE id = ?' )->set( $this->arrValues )->execute( $this->strItemID );
                }

                if ( $blnReload ) {

                    $this->reload();
                }

                else {

                    $this->redirectAfterInsertion( $this->strRedirectID, $strQuery );
                }

                break;

            case 'copy':

                unset( $this->arrValues['id'] );

                $this->SQLBuilder->Database->prepare( 'INSERT INTO '. $this->catalogTablename .' %s' )->set( $this->arrValues )->execute();

                if ( $this->catalogNotifyInsert ) {

                    $objCatalogNotification = new CatalogNotification();
                    $objCatalogNotification->notifyOnInsert( $this->catalogNotifyInsert, $this->arrValues );
                }

                $arrData = [

                    'id' => '',
                    'row' => $this->arrValues,
                    'table' => $this->catalogTablename
                ];

                $this->CatalogEvents->addEventListener( 'create', $arrData );
                $this->redirectAfterInsertion( $this->strRedirectID, $strQuery );

                break;
        }
    }


    protected function redirectAfterInsertion( $intPage, $strAttributes = '', $blnReturn=false ) {

        if ( ( $intPage = intval($intPage ) ) <= 0 ) {

            return '';
        }

        $objPage = \PageModel::findWithDetails( $intPage );
        $strUrl = $this->generateFrontendUrl( $objPage->row(), '', $objPage->language, true );

        if ( strncmp( $strUrl, 'http://', 7 ) !== 0 && strncmp( $strUrl, 'https://', 8 ) !== 0 ) {

            $strUrl = \Environment::get( 'base' ) . $strUrl;
        }

        if ( $strAttributes ) {

            $strUrl .= $strAttributes;
        }

        if ( !$blnReturn ) {

            $this->redirect( $strUrl );
        }

        return $strUrl;
    }


    protected function prepareData() {

        if ( !empty( $this->arrValues ) && is_array( $this->arrValues ) ) {

            foreach ( $this->arrValues as $strFieldname => $varValue ) {

                $arrField = $this->arrFormFields[ $strFieldname ];
                $varValue = Toolkit::prepareValue4Db( $varValue );

                if ( $arrField['_type'] == 'date' || in_array( $arrField['eval']['rgxp'], [ 'date', 'time', 'datim' ] ) ) {

                    $objDate = new \Date( $varValue );
                    $intTime = $objDate->timestamp;
                    $varValue = $intTime < 1 ? '' : $intTime;
                }

                if ( strpos( $arrField['sql'], 'int' ) && is_string( $varValue ) ) {

                    $varValue = intval( $varValue );
                }

                $this->arrValues[ $strFieldname ] = $varValue;
            }
        }
    }


    protected function setValues() {

        if ( $this->strItemID && $this->catalogTablename ) {
            
            $this->arrValues = $this->SQLQueryHelper->getCatalogTableItemByID( $this->catalogTablename, $this->strItemID );
        }

        if ( !empty( $this->arrValues ) && is_array( $this->arrValues ) ) {

            foreach ( $this->arrValues as $strKey => $varValue ) {

                $this->arrValues[$strKey] = \Input::post( $strKey ) !== null ? \Input::post( $strKey ) : $varValue;
            }
        }
    }


    protected function setOptions() {

        if ( !empty( $this->arrOptions ) && is_array( $this->arrOptions ) ) {

            foreach ( $this->arrOptions as $strKey => $varValue ) {

                $this->{$strKey} = $varValue;
            }
        }
    }


    protected function decodeValue( $varValue ) {

        if ( class_exists('StringUtil') ) {

            $varValue = \StringUtil::decodeEntities( $varValue );
        }

        else {

            $varValue = \Input::decodeEntities( $varValue );
        }

        return $varValue;
    }


    protected function getCaptcha() {

        $arrCaptcha = [

            'id' => 'id_',
            'required' => true,
            'type' => 'captcha',
            'mandatory' => true,
            'tableless' => '1',
            'label' => $GLOBALS['TL_LANG']['MSC']['securityQuestion']
        ];

        $strClass = $GLOBALS['TL_FFL']['captcha'];

        if ( !class_exists( $strClass ) ) {

            $strClass = 'FormCaptcha';
        }

        $objCaptcha = new $strClass( $arrCaptcha );

        if ( \Input::post('FORM_SUBMIT') == $this->strSubmitName ) {

            $objCaptcha->validate();

            if ( $objCaptcha->hasErrors() ) {

                $this->blnNoSubmit = true;
            }
        }

        return $objCaptcha;
    }


    protected function convertWidgetToField( $arrField ) {

        if ( $arrField['inputType'] == 'checkboxWizard' ) {

            $arrField['inputType'] = 'checkbox';
        }

        if ( $arrField['inputType'] == 'fileTree' ) {

            $arrField['inputType'] = 'upload';
        }

        if ( $arrField['inputType'] == 'catalogMessageWidget' ) {

            $arrField['inputType'] = 'catalogMessageForm';
        }

        $arrField['eval']['tableless'] = '1';
        $arrField['eval']['required'] = $arrField['eval']['mandatory'];

        return $arrField;
    }


    protected function fieldClassExist( $strInputType ) {

        $strClass = $GLOBALS['TL_FFL'][$strInputType];

        if ( !class_exists( $strClass ) ) {

            return false;
        }

        return $strClass;
    }
}