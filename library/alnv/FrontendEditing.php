<?php

namespace CatalogManager;

class FrontendEditing extends CatalogController {


    public $strAct = '';
    public $strItemID = '';
    public $arrOptions = [];
    public $strTemplate = '';

    protected $arrValues = [];
    protected $strFormId = '';
    protected $arrCatalog = [];
    protected $arrPalettes = [];
    protected $strOnChangeId = '';
    protected $strRedirectID = '';
    protected $objTemplate = null;
    protected $blnNoSubmit = false;
    protected $blnHasUpload = false;
    protected $arrCatalogFields = [];
    protected $arrPaletteLabels = [];
    protected $arrCatalogAttributes = [];
    protected $arrValidFormTemplates = [];


    public function __construct() {

        parent::__construct();

        $this->import( 'CatalogEvents' );
        $this->import( 'CatalogMessage' );
        $this->import( 'SQLQueryHelper' );
        $this->import( 'SQLQueryBuilder' );
        $this->import( 'CatalogFineUploader' );
        $this->import( 'CatalogFieldBuilder' );
        $this->import( 'I18nCatalogTranslator' );
    }


    public function initialize() {

        global $objPage;

        $this->setOptions();
        
        \System::loadLanguageFile('catalog_manager');

        $strPalette = 'general_legend';
        $this->strFormId = md5( 'id_' . $this->catalogTablename );
        $this->strOnChangeId = md5( 'change_' . $this->catalogTablename );
        $this->arrValidFormTemplates = array_keys( Toolkit::$arrFormTemplates );
        $this->catalogDefaultValues = Toolkit::deserialize( $this->catalogDefaultValues );
        $this->catalogItemOperations = Toolkit::deserialize( $this->catalogItemOperations );
        $this->catalogExcludedFields = Toolkit::deserialize( $this->catalogExcludedFields );

        $this->CatalogFieldBuilder->initialize(  $this->catalogTablename );

        $this->arrCatalog = $this->CatalogFieldBuilder->getCatalog();
        $arrCatalogFields = $this->CatalogFieldBuilder->getCatalogFields( true, $this );


        if ( !empty( $arrCatalogFields ) && is_array( $arrCatalogFields ) ) {

            foreach ( $arrCatalogFields as $arrField ) {

                $strPalette = $arrField['_palette'] && !in_array( $arrField['fieldname'], $this->catalogExcludedFields ) ? $arrField['_palette'] : $strPalette;

                if ( $arrField['type'] == 'fieldsetStart' ) {

                    $strPalette = $arrField['title'];
                    $this->arrPaletteLabels[ $strPalette ] = $this->I18nCatalogTranslator->get( 'legend', $arrField['title'], [ 'title' => $arrField['label'] ] );
                }

                $arrField['_palette'] = $strPalette;

                if ( Toolkit::isEmpty( $arrField['type'] ) ) continue;
                if ( Toolkit::isEmpty( $arrField['fieldname'] ) || !Toolkit::isDcConformField( $arrField ) ) continue;

                if ( $arrField['type'] == 'hidden' ) $arrField['_dcFormat']['inputType'] = 'hidden';

                if ( $arrField['type'] == 'upload' && $arrField['useFineUploader'] ) {

                    $arrField['_dcFormat']['inputType'] = 'catalogFineUploader';
                    $this->CatalogFineUploader->loadAssets();
                }

                $this->arrCatalogFields[ $arrField['fieldname'] ] = $arrField;
            }
        }

        if ( $this->strItemID && $this->strAct && in_array( $this->strAct, [ 'copy', 'edit', 'pdf', 'delete' ] ) ) {

            $this->setValues();
        }

        $this->arrPaletteLabels['general_legend'] = $this->I18nCatalogTranslator->get( 'legend', 'general_legend' );
        $this->arrPaletteLabels['invisible_legend'] =  $this->I18nCatalogTranslator->get( 'legend', 'invisible_legend' );

        $this->setCatalogAttributes();
        $this->setPalettes();

        if ( $this->catalogFormRedirect && $this->catalogFormRedirect !== '0' ) {

            $this->strRedirectID = $this->catalogFormRedirect;
        }

        else {

            $this->strRedirectID = $objPage->id;
        }

        if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerInitializeFrontendEditing'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerInitializeFrontendEditing'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['catalogManagerInitializeFrontendEditing'] as $callback ) {

                $this->import( $callback[0] );
                $this->{$callback[0]}->{$callback[1]}( $this->catalogTablename, $this->arrCatalog, $this->arrCatalogFields, $this->arrValues, $this );
            }
        }
    }


    public function render() {

        $this->objTemplate = new \FrontendTemplate( $this->strTemplate );
        $this->objTemplate->setData( $this->arrOptions );
        $arrCategories = [];

        if ( !is_array( $this->catalogExcludedFields ) ) {

            $this->catalogExcludedFields = [];
        }

        if ( !empty( $this->arrPalettes ) && is_array( $this->arrPalettes ) ) {

            foreach ( $this->arrPalettes as $strPalette => $arrFieldNames ) {

                if ( !empty( $arrFieldNames ) && is_array( $arrFieldNames ) ) {

                    $strLegend = $this->arrPaletteLabels[ $strPalette ];
                    $arrCategories[ $strLegend ] = $this->renderFieldsByPalette( $arrFieldNames, $strPalette );
                }
            }
        }

        if ( !$this->disableCaptcha ) {

            $objCaptcha = $this->getCaptcha();
            $this->objTemplate->captchaWidget = $objCaptcha->parse();
        }

        if ( !$this->blnNoSubmit && \Input::post('FORM_DELETE_IMAGE' ) ) {

            $this->deleteImage();
        }

        if ( !$this->blnNoSubmit && \Input::post('FORM_SUBMIT') == $this->strFormId ) {

            $this->saveEntity();
        }

        if ( \Input::post('FORM_SUBMIT_BACK') == $this->strFormId ) {

            global $objPage;

            if ( !$this->catalogFrontendEditingViewPage ) {

                $this->catalogFrontendEditingViewPage = $objPage->id;
            }

            $strQuery = '';

            if ( \Input::get( 'pid' ) ) {

                $strQuery .= '?pid=' . \Input::get( 'pid' );
            }

            $this->redirectAfterInsertion( $this->catalogFrontendEditingViewPage, $strQuery );
        }

        $this->objTemplate->method = 'POST';
        $this->objTemplate->formId = $this->strFormId;
        $this->objTemplate->categories = $arrCategories;
        $this->objTemplate->onChangeId = $this->strOnChangeId;
        $this->objTemplate->catalogAttributes = $this->arrCatalogAttributes;
        $this->objTemplate->action = \Environment::get( 'indexFreeRequest' );
        $this->objTemplate->message = $this->CatalogMessage->get( $this->id );
        $this->objTemplate->attributes = $this->catalogNoValidate ? 'novalidate' : '';
        $this->objTemplate->back = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['back'];
        $this->objTemplate->submit = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['submit'];
        $this->objTemplate->captchaLabel = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['captchaLabel'];
        $this->objTemplate->enctype = $this->blnHasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';

        return $this->objTemplate->parse();
    }


    protected function deleteImage() {

        $this->import( 'SQLBuilder' );

        $strTempUuid = '';
        $strFieldname = \Input::post('FORM_DELETE_IMAGE' );

        if ( $strFieldname && isset( $this->arrValues[ $strFieldname ] ) ) {

            $strTempUuid = $this->arrValues[ $strFieldname ];
            $this->arrValues[ $strFieldname ] = '';
        }

        switch ( $this->strAct ) {

            case 'create':

                //

                break;

            case 'copy':
            case 'edit':

                $this->SQLBuilder->Database->prepare( sprintf( 'UPDATE %s SET %s = "" WHERE id = ?', $this->catalogTablename, $strFieldname ) )->execute( $this->strItemID );
                $objFile = \FilesModel::findByUuid( $strTempUuid );

                if ( $objFile !== null ) {

                    if ( file_exists( TL_ROOT . '/' . $objFile->path ) ) {

                        unlink( TL_ROOT . '/' . $objFile->path );
                    }
                }

                break;
        }

        $this->reload();
    }


    protected function setCatalogAttributes() {

        if ( !empty( $this->arrCatalogFields ) && is_array( $this->arrCatalogFields  ) ) {

            $this->arrCatalogAttributes = Toolkit::parseCatalogValues( $this->arrValues, $this->arrCatalogFields, false );
        }
    }


    protected function renderFieldsByPalette( $arrFieldNames, $strPalette = '' ) {

        $arrReturn = [];

        foreach ( $arrFieldNames as $strFieldname ) {

            if ( in_array( $strFieldname, $this->catalogExcludedFields ) ) continue;

            $arrField = $this->arrCatalogFields[ $strFieldname ]['_dcFormat'];
            $arrField = $this->convertWidgetToField( $arrField );

            $strClass = $this->fieldClassExist( $arrField['inputType'] );

            if ( $strClass === false ) continue;

            $arrData = $strClass::getAttributesFromDca( $arrField, $strFieldname, $arrField['default'], '', '' );

            if ( is_bool( $arrField['_disableFEE'] ) && $arrField['_disableFEE'] == true ) continue;

            if ( $arrField['inputType'] == 'catalogFineUploader' ) {

                $arrData['configAttributes'] = [

                    'storeFile' => $this->catalogStoreFile,
                    'useHomeDir' => $this->catalogUseHomeDir,
                    'uploadFolder' => $this->catalogUploadFolder,
                    'doNotOverwrite' => $this->catalogDoNotOverwrite
                ];
            }

            if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerModifyFrontendEditingField'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerModifyFrontendEditingField'] ) ) {

                foreach ( $GLOBALS['TL_HOOKS']['catalogManagerModifyFrontendEditingField'] as $callback ) {

                    $this->import( $callback[0] );
                    $this->{$callback[0]}->{$callback[1]}( $strFieldname, $strClass, $arrData, $this->arrCatalogFields[ $strFieldname ], $this->arrCatalog );
                }
            }

            $objWidget = new $strClass( $arrData );
            $objWidget->storeValues = true;
            $objWidget->id = 'id_' . $strFieldname;
            $objWidget->value = $this->arrValues[ $strFieldname ];
            $objWidget->placeholder = $arrField['_placeholder'] ? $arrField['_placeholder'] : '';
            $objWidget->description = is_array( $arrField['label'] ) && isset( $arrField['label'][1] ) ? $arrField['label'][1] : '';

            if ( $this->arrCatalogFields[ $strFieldname ]['template'] && in_array( $this->arrCatalogFields[ $strFieldname ]['type'], $this->arrValidFormTemplates ) ) $objWidget->template = $this->arrCatalogFields[ $strFieldname ]['template'];

            if ( is_array( $arrField['_cssID'] ) && ( $arrField['_cssID'][0] || $arrField['_cssID'][1] ) ) {

                if ( $arrField['_cssID'][0] ) $objWidget->id = 'id_' . $arrField['_cssID'][0];
                if ( $arrField['_cssID'][1] ) $objWidget->class = ' ' . $arrField['_cssID'][1];
            }

            if ( $this->strAct == 'copy' && $arrField['eval']['doNotCopy'] === true ) {

                $objWidget->value = '';
            }

            if ( $arrField['eval']['multiple'] && $arrField['eval']['csv'] && is_string( $objWidget->value ) ) {

                $objWidget->value = explode( $arrField['eval']['csv'], $objWidget->value );
            }

            if ( $arrField['eval']['submitOnChange'] ) {

                $objWidget->addAttributes([ 'onchange' => 'this.form.submit()' ]);
            }

            if ( $arrField['inputType'] == 'upload' || $arrField['inputType'] == 'catalogFineUploader' ) {

                $objWidget->storeFile = $this->catalogStoreFile;
                $objWidget->useHomeDir = $this->catalogUseHomeDir;
                $objWidget->maxlength = $arrField['eval']['maxsize'];
                $objWidget->multiple = $arrField['eval']['multiple'];
                $objWidget->uploadFolder = $this->catalogUploadFolder;
                $objWidget->extensions = $arrField['eval']['extensions'];
                $objWidget->doNotOverwrite = $this->catalogDoNotOverwrite;
                $objWidget->preview = $this->arrCatalogAttributes[ $strFieldname ];
                $objWidget->deleteLabel = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['deleteImageButton'];

                $this->blnHasUpload = true;
            }

            if ( $arrField['inputType'] == 'textarea' && isset( $arrField['eval']['rte'] ) ) {

                $objWidget->mandatory = false;
                $arrTextareaData = [ 'selector' => 'ctrl_' . $objWidget->id ];

                if ( version_compare( VERSION, '4.0', '>=' ) ) {

                    $strTemplate = 'be_' . $arrField['eval']['rte'];
                }

                else {

                    $strTemplate = 'ctlg_catalog_tinyMCE';
                    $arrTextareaData['tinyMCE'] = TL_ROOT . '/' . 'system/config/' . $arrField['eval']['rte'] . '.php';
                }

                $objScript = new \FrontendTemplate( $strTemplate );
                $objScript->setData( $arrTextareaData );
                $strScript = $objScript->parse();

                $GLOBALS['TL_HEAD'][] = $strScript;
            }

            
            if ( $this->arrCatalogFields[ $strFieldname ]['autoCompletionType'] ) {

                $objWidget->class .= ' awesomplete-field';
                $objWidget->class .= ( $arrField['multiple'] ? ' multiple' : '' );

                if ( \Input::get( 'ctlg_autocomplete_query' ) && \Input::get('ctlg_fieldname') == $strFieldname  ) {

                    $this->sendJsonResponse( $this->arrCatalogFields[ $strFieldname ], $this->id, \Input::get( 'ctlg_autocomplete_query' ) );
                }

                $objScriptLoader = new CatalogScriptLoader();
                $objScriptLoader->loadScript( 'awesomplete-frontend' );
                $objScriptLoader->loadStyle( 'awesomplete' );
            }
            
            if ( Toolkit::isEmpty( $objWidget->value ) && !Toolkit::isEmpty( $arrField['default'] ) ) {

                $objWidget->value = $arrField['default'];
            }

            if ( $arrField['eval']['rgxp'] && in_array( $arrField['eval']['rgxp'], [ 'date', 'time', 'datim' ] ) ) {

                $strDateFormat = \Date::getFormatFromRgxp( $arrField['eval']['rgxp'] );
                $objWidget->value = $objWidget->value ? \Date::parse( $strDateFormat, $objWidget->value ) : '';
            }

            if ( in_array( $arrField['_type'], [ 'hidden', 'date' ] ) && $this->arrCatalogFields[ $strFieldname ]['tstampAsDefault'] ) {

                if ( Toolkit::isEmpty( $objWidget->value ) ) {

                    $objWidget->value = time();
                }
            }

            if ( \Input::post('FORM_SUBMIT') == $this->strOnChangeId && !Toolkit::isEmpty( \Input::post( $strFieldname ) ) ) {

                $objWidget->value = \Input::post( $strFieldname );
            }

            $objWidget->catalogAttributes = $this->arrCatalogAttributes;

            if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerModifyFrontendEditingWidgetBeforeParsing'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerModifyFrontendEditingWidgetBeforeParsing'] ) ) {

                foreach ( $GLOBALS['TL_HOOKS']['catalogManagerModifyFrontendEditingWidgetBeforeParsing'] as $callback ) {

                    $this->import( $callback[0] );
                    $this->{$callback[0]}->{$callback[1]}( $objWidget, $strFieldname, $this->arrCatalogFields, $this->arrCatalog, $arrData, $this );
                }
            }

            if ( \Input::post('FORM_SUBMIT') == $this->strFormId ) {

                $objWidget->validate();
                $varValue = $objWidget->value;

                if ( Toolkit::isEmpty( $varValue ) && $arrField['inputType'] == 'catalogFineUploader' ) {

                    $varValue = $this->arrValues[ $strFieldname ];
                }

                if ( Toolkit::isEmpty( $varValue ) && !Toolkit::isEmpty( $arrField['default'] ) ) {

                    $varValue = $arrField['default'];
                }

                if ( $varValue && is_string( $varValue ) ) {

                    $varValue = $this->decodeValue( $varValue );
                    $varValue = $this->replaceInsertTags( $varValue );
                }

                if ( $varValue != '' && in_array( $arrField['eval']['rgxp'], [ 'date', 'time', 'datim' ] ) ) {

                    try {

                        $objDate = new \Date( $varValue, \Date::getFormatFromRgxp( $arrField['eval']['rgxp'] ) );
                        $varValue = $objDate->tstamp;

                    } catch ( \OutOfBoundsException $objError ) {

                        $objWidget->addError( sprintf( $GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue ) );
                    }
                }

                if ( $arrField['eval']['unique'] && $varValue != '' && !$this->SQLQueryHelper->SQLQueryBuilder->Database->isUniqueValue( $this->catalogTablename, $strFieldname, $varValue, ( $this->strAct == 'edit' ? $this->strItemID : null ) ) ) {

                    $objWidget->addError( sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $arrField['label'][0] ?: $strFieldname ) );
                }

                if ( $objWidget->submitInput() && !$objWidget->hasErrors() && is_array( $arrField['save_callback'] ) ) {

                    foreach ( $arrField['save_callback'] as $arrCallback ) {

                        $objDataContainer = new CatalogDataContainer( $this->catalogTablename );

                        $objDataContainer->value = $varValue;
                        $objDataContainer->id = $this->strItemID;
                        $objDataContainer->field = $strFieldname;
                        $objDataContainer->activeRecord = $this->arrValues;
                        $objDataContainer->ptable = $this->arrCatalog['pTable'];
                        $objDataContainer->ctable = $this->arrCatalog['cTables'];

                        try {

                            if ( is_array( $arrCallback ) ) {

                                $this->import( $arrCallback[0] );
                                $varValue = $this->{$arrCallback[0]}->{$arrCallback[1]}( $varValue, $objDataContainer );
                            }

                            elseif( is_callable( $arrCallback ) ) {

                                $varValue = $arrCallback( $varValue, $objDataContainer );
                            }
                        }

                        catch ( \Exception $objError ) {

                            $objWidget->class = 'error';
                            $objWidget->addError( $objError->getMessage() );
                        }
                    }
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

                    $this->arrValues[ $strFieldname ] = $varValue;
                }

                $arrFiles = $_SESSION['FILES'];

                if ( isset( $arrFiles[$strFieldname] ) && is_array( $arrFiles[$strFieldname] ) && $this->catalogStoreFile ) {

                    if ( !Toolkit::isAssoc( $arrFiles[$strFieldname] ) ) {

                        $arrUUIDValues = [];

                        foreach ( $arrFiles[$strFieldname] as $arrFile ) {

                            $arrUUIDValues[] = $this->getFileUUID( $arrFile );
                        }

                        $strUUIDValue = serialize( $arrUUIDValues );
                    }

                    else {

                        $strUUIDValue = $this->getFileUUID( $arrFiles[$strFieldname] );
                    }

                    $this->arrValues[$strFieldname] = $strUUIDValue;

                    unset( $_SESSION['FILES'][$strFieldname] );
                }
            }

            $arrReturn[] = $objWidget->parse();
        }

        return $arrReturn;
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

        if ( $this->hasVisibility() ) {

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

        if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingQuery'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingQuery'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingQuery'] as $arrCallback )  {

                if ( is_array( $arrCallback ) ) {

                    $this->import( $arrCallback[0] );
                    $arrQuery = $this->{$arrCallback[0]}->{$arrCallback[1]}( $arrQuery, $this );
                }
            }
        }

        $objEntities = $this->SQLQueryBuilder->execute( $arrQuery );

        return $objEntities->numRows ? true : false;
    }


    protected function hasVisibility() {

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


    public function checkAccess() {

        $this->import( 'FrontendEditingPermission' );

        $this->FrontendEditingPermission->blnDisablePermissions = $this->catalogEnableFrontendPermission ? false : true;
        $this->FrontendEditingPermission->initialize();

        return $this->FrontendEditingPermission->hasAccess( $this->catalogTablename );
    }


    public function checkPermission( $strMode ) {

        $this->import( 'FrontendEditingPermission' );

        if ( !is_array( $this->catalogItemOperations ) ) $this->catalogItemOperations = [];
        if ( !in_array( $strMode, $this->catalogItemOperations ) ) return false;

        $this->FrontendEditingPermission->blnDisablePermissions = $this->catalogEnableFrontendPermission ? false : true;
        $this->FrontendEditingPermission->initialize();

        if ( $strMode == 'copy' ) $strMode = 'create';

        return $this->FrontendEditingPermission->hasPermission( $strMode, $this->catalogTablename );
    }


    public function deleteEntity() {

        $this->import( 'SQLBuilder' );

        if (  $this->SQLBuilder->Database->tableExists( $this->catalogTablename ) ) {

            if ( $this->catalogNotifyDelete ) {

                $objCatalogNotification = new CatalogNotification( $this, $this->strItemID );
                $objCatalogNotification->notifyOnDelete( $this->catalogNotifyDelete, [] );
            }

            $arrData = [

                'row' => [],
                'id' => $this->strItemID,
                'table' => $this->catalogTablename
            ];

            $this->CatalogMessage->set( 'deleteMessage', $arrData, $this->id );
            $this->CatalogEvents->addEventListener( 'delete', $arrData, $this );
            $this->SQLBuilder->Database->prepare( sprintf( 'DELETE FROM %s WHERE id = ? ', $this->catalogTablename ) )->execute( $this->strItemID );
            \System::log( 'DELETE FROM '. $this->catalogTablename .' WHERE id='. $this->strItemID, __METHOD__, TL_GENERAL );
            // $this->deleteChildEntities( $this->catalogTablename, $this->strItemID );
        }

        $strAttributes = '';
        $objPage = \PageModel::findWithDetails( $this->strRedirectID );
        $strUrl = $this->generateFrontendUrl( $objPage->row(), '', $objPage->language, true );

        if ( strncmp( $strUrl, 'http://', 7 ) !== 0 && strncmp( $strUrl, 'https://', 8 ) !== 0 ) $strUrl = \Environment::get( 'base' ) . $strUrl;
        if ( \Input::get( 'pid' ) ) $strAttributes .= '?pid=' .\Input::get( 'pid' );
        if ( $strAttributes ) $strUrl .= $strAttributes;

        if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingRedirect'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingRedirect'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingRedirect'] as $arrCallback ) {

                if ( is_array( $arrCallback ) ) {

                    $this->import( $arrCallback[0] );
                    $strUrl = $this->{$arrCallback[0]}->{$arrCallback[1]}( $strUrl, $strAttributes, $objPage, $this->arrValues, $this->strAct, $this->id, $this->catalogTablename, $this );
                }
            }
        }

        $this->redirect( $strUrl );
    }
    

    protected function deleteChildEntities( $strTable, $strID, $strPtable = '' ) {

        if ( !isset( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ] ) ) return null;

        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ];
        $arrTables = $arrCatalog['cTables'];

        if ( is_array( $arrTables ) && !empty( $arrTables ) ) {

            foreach ( $arrTables as $strTable ) {

                $arrValues = [ $strID ];

                if ( $strTable == 'tl_content' ) $strPtable = $strTable;
                if ( $strPtable ) $arrValues[] = $strPtable;

                if ( !$strPtable ) {

                    $objEntity = $this->SQLBuilder->Database->prepare( sprintf( 'SELECT id FROM %s WHERE pid = ?', $strTable ) )->limit(1)->execute( $strID );

                    $this->deleteChildEntities( $strTable, $objEntity->id );
                }

                $this->SQLBuilder->Database->prepare( sprintf( 'DELETE FROM %s WHERE pid = ?' . ( $strPtable ? ' AND ptable = ?' : '' ), $strTable ) )->execute( $arrValues );
            }
        }
    }


    protected function setPalettes() {

        $this->arrPalettes = [

            'general_legend' => []
        ];

        if ( !empty( $this->arrCatalogFields ) && is_array( $this->arrCatalogFields ) ) {

            foreach ( $this->arrCatalogFields as $strFieldname => $arrField ) {

                $strPalette = $arrField['_palette'];

                if ( Toolkit::isEmpty( $strPalette ) ) continue;

                if ( !is_array( $this->arrPalettes[ $strPalette ] ) ) {

                    $this->arrPalettes[ $strPalette ] = [];
                }

                $this->arrPalettes[ $strPalette ][] = $strFieldname;
            }
        }

        if ( !in_array( 'invisible', $this->arrCatalog['operations'] ) ) {

            unset( $this->arrPalettes['invisible_legend'] );
        }

        else {

            $arrPalettes = array_keys( $this->arrPalettes );

            if ( in_array( 'invisible_legend', $arrPalettes ) ) {

                $arrInvisiblePalette = $this->arrPalettes['invisible_legend'];

                unset( $this->arrPalettes['invisible_legend'] );

                $this->arrPalettes['invisible_legend'] = $arrInvisiblePalette;
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

        if ( !class_exists( $strClass ) ) $strClass = 'FormCaptcha';

        $objCaptcha = new $strClass( $arrCaptcha );

        if ( \Input::post('FORM_SUBMIT') == $this->strFormId ) {

            $objCaptcha->validate();

            if ( $objCaptcha->hasErrors() ) $this->blnNoSubmit = true;
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

        $strClass = $GLOBALS['TL_FFL'][ $strInputType ];
        if ( !class_exists( $strClass ) ) return false;

        return $strClass;
    }


    protected function setValues() {

        if ( $this->strItemID && $this->catalogTablename ) {

            $this->arrValues = $this->SQLQueryHelper->getCatalogTableItemByID( $this->catalogTablename, $this->strItemID );
        }

        if ( !empty( $this->arrValues ) && is_array( $this->arrValues ) ) {

            foreach ( $this->arrValues as $strFieldname => $varValue ) {

                $this->arrValues[ $strFieldname ] = \Input::post( $strFieldname ) !== null ? \Input::post( $strFieldname ) : $varValue;
            }
        }
    }


    protected function decodeValue( $varValue ) {

        return \StringUtil::decodeEntities( $varValue );
    }


    protected function getFileUUID( $arrFile ) {

        $strRoot = TL_ROOT . '/';
        $strUuid = $arrFile['uuid'];
        $strFile = substr( $arrFile['tmp_name'], strlen( $strRoot ) );
        $objFiles = \FilesModel::findByPath( $strFile );

        if ( $objFiles !== null ) {

            $strUuid = $objFiles->uuid;
        }

        return $strUuid;
    }


    protected function saveEntity() {

        $strQuery = '';
        $this->import( 'SQLBuilder' );

        if ( $this->arrCatalog['useGeoCoordinates'] ) {

            $this->getGeoCordValues();
        }

        if ( \Input::get('pid') ) {

            $strQuery = sprintf( '?pid=%s', \Input::get('pid') );
        }

        if ( isset( $this->arrValues['tstamp'] ) ) {

            $this->arrValues['tstamp'] = (string)time();
        }

        if ( is_array( $this->catalogDefaultValues ) && $this->catalogDefaultValues[0] ) {

            foreach ( $this->catalogDefaultValues as $arrDefaultValue ) {

                $strKeyname = $arrDefaultValue['key'];
                $strValue = $this->replaceInsertTags( $arrDefaultValue['value'] );

                if ( Toolkit::isEmpty( $strKeyname ) || Toolkit::isEmpty( $strValue ) ) continue;

                if ( Toolkit::isEmpty( $this->arrValues[ $strKeyname ] ) ) {

                    $this->arrValues[ $strKeyname ] = $strValue;
                }
            }
        }

        foreach ( $this->arrCatalogFields as $strFieldname => $arrField ) {

            if ( !Toolkit::isEmpty( $arrField['dynValue'] ) ) {

                $this->arrValues[ $strFieldname ] = Toolkit::generateDynValue( $arrField['dynValue'], Toolkit::prepareValues4Db( $this->arrValues ) );
                if ( $strFieldname == 'title' && Toolkit::hasDynAlias() ) $this->arrValues['alias'] = '';
            }
        }

        $objDcCallbacks = new DcCallbacks();
        $this->arrValues['alias'] = $objDcCallbacks->generateFEAlias( $this->arrValues['alias'], $this->arrValues['title'], $this->catalogTablename, $this->arrValues['id'], $this->id );

        if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingOnSave'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingOnSave'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingOnSave'] as $arrCallback )  {

                if ( is_array( $arrCallback ) ) {

                    $this->import( $arrCallback[0] );
                    $this->arrValues = $this->{$arrCallback[0]}->{$arrCallback[1]}( $this->arrValues, $this->strAct, $this->arrCatalog, $this->arrCatalogFields, $this );
                }
            }
        }

        $this->prepare();

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

                $strInsertId = $this->SQLBuilder->Database->prepare( 'INSERT INTO '. $this->catalogTablename .' %s' )->set( $this->arrValues )->execute()->insertId;
                \System::log( 'A new entry "'. $this->catalogTablename .'='. $strInsertId .'" has been created', __METHOD__, TL_GENERAL );

                $this->arrValues['id'] = $strInsertId;

                if ( $this->catalogNotifyInsert ) {

                    $objCatalogNotification = new CatalogNotification( $this );
                    $objCatalogNotification->notifyOnInsert( $this->catalogNotifyInsert, $this->arrValues );
                }

                $arrData = [

                    'id' => $strInsertId,
                    'row' => $this->arrValues,
                    'table' => $this->catalogTablename,
                ];

                $this->CatalogMessage->set( 'insertMessage', $arrData, $this->id );
                $this->CatalogEvents->addEventListener( 'create', $arrData, $this );

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

                        $objCatalogNotification = new CatalogNotification( $this, $this->strItemID );
                        $objCatalogNotification->notifyOnUpdate( $this->catalogNotifyUpdate, $this->arrValues );
                    }

                    $arrData = [

                        'id' => $this->strItemID,
                        'row' => $this->arrValues,
                        'table' => $this->catalogTablename,
                    ];

                    $this->CatalogMessage->set( 'updateMessage', $arrData, $this->id );
                    $this->CatalogEvents->addEventListener( 'update', $arrData, $this );
                    $this->SQLBuilder->Database->prepare( 'UPDATE '. $this->catalogTablename .' %s WHERE id = ?' )->set( $this->arrValues )->execute( $this->strItemID );

                    \System::log( 'An entry "'. $this->catalogTablename .'='. $this->strItemID .'" has been updated', __METHOD__, TL_GENERAL );
                }

                if ( !$this->isVisible() ) $blnReload =  false;

                if ( $blnReload && ( Toolkit::isEmpty( $this->catalogFormRedirect ) || $this->catalogFormRedirect == '0'  ) ) {

                    $this->reload();
                }

                else {

                    $this->redirectAfterInsertion( $this->strRedirectID, $strQuery );
                }

                break;

            case 'copy':

                unset( $this->arrValues['id'] );

                $objInsert = $this->SQLBuilder->Database->prepare( 'INSERT INTO '. $this->catalogTablename .' %s' )->set( $this->arrValues )->execute();

                \System::log( 'An entry "'. $this->catalogTablename .'='. $objInsert->insertId .'" has been duplicated', __METHOD__, TL_GENERAL );

                if ( $this->catalogNotifyDuplicate ) {

                    $objCatalogNotification = new CatalogNotification( $this, $this->strItemID );
                    $objCatalogNotification->notifyOnUpdate( $this->catalogNotifyDuplicate, $this->arrValues );
                }

                $arrData = [

                    'id' => '',
                    'row' => $this->arrValues,
                    'table' => $this->catalogTablename
                ];

                $this->CatalogMessage->set( 'insertMessage', $arrData, $this->id );
                $this->CatalogEvents->addEventListener( 'create', $arrData, $this );
                $this->redirectAfterInsertion( $this->strRedirectID, $strQuery );

                break;
        }
    }


    protected function redirectAfterInsertion( $intPage, $strAttributes = '', $blnReturn = false ) {

        if ( ( $intPage = intval($intPage ) ) <= 0 ) return '';

        $objPage = \PageModel::findWithDetails( $intPage );
        $strUrl = $this->generateFrontendUrl( $objPage->row(), '', $objPage->language, true );

        if ( strncmp( $strUrl, 'http://', 7 ) !== 0 && strncmp( $strUrl, 'https://', 8 ) !== 0 ) $strUrl = \Environment::get( 'base' ) . $strUrl;
        if ( $strAttributes ) $strUrl .= $strAttributes;

        if ( $this->catalogFormRedirectParameter ) {

            $strParameters = \StringUtil::parseSimpleTokens( $this->catalogFormRedirectParameter, $this->arrValues );
            $strUrl .= $strAttributes ? '&' . $strParameters : '?' . $strParameters;
        }

        if ( !$blnReturn ) {

            if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingRedirect'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingRedirect'] ) ) {

                foreach ( $GLOBALS['TL_HOOKS']['catalogManagerFrontendEditingRedirect'] as $arrCallback ) {

                    if ( is_array( $arrCallback ) ) {

                        $this->import( $arrCallback[0] );
                        $strUrl = $this->{$arrCallback[0]}->{$arrCallback[1]}( $strUrl, $strAttributes, $objPage, $this->arrValues, $this->strAct, $this->id, $this->catalogTablename, $this );
                    }
                }
            }

            $this->redirect( $strUrl );
        }

        return $strUrl;
    }


    protected function getGeoCordValues() {

        $arrCords = [];
        $objGeoCoding = new GeoCoding();
        $strGeoInputType = $this->arrCatalog['addressInputType'];

        switch ( $strGeoInputType ) {

            case 'useSingleField':

                $arrCords = $objGeoCoding->getCords( $this->arrValues[ $this->arrCatalog['geoAddress'] ], 'en', true );

                break;

            case 'useMultipleFields':

                $objGeoCoding->setCity( $this->arrValues[ $this->arrCatalog['geoCity'] ] );
                $objGeoCoding->setStreet( $this->arrValues[ $this->arrCatalog['geoStreet'] ] );
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


    protected function prepare() {

        if ( !empty( $this->arrValues ) && is_array( $this->arrValues ) ) {

            foreach ( $this->arrValues as $strFieldname => $varValue ) {

                $arrField = $this->arrCatalogFields[ $strFieldname ]['_dcFormat'];
                $varValue = Toolkit::prepareValue4Db( $varValue );

                if ( is_null( $arrField ) ) continue;

                if ( $arrField['_type'] == 'date' || in_array( $arrField['eval']['rgxp'], [ 'date', 'time', 'datim' ] ) ) {

                    $objDate = new \Date( $varValue );
                    $intTime = $objDate->timestamp;
                    $varValue = $intTime < 1 ? '' : $intTime;
                }

                if ( strpos( $arrField['sql'], 'int' ) !== false && is_string( $varValue ) ) {

                    $varValue = intval( $varValue );
                }

                $this->arrValues[ $strFieldname ] = $varValue;
            }
        }
    }


    public function getCatalog() {

        return is_array( $this->arrCatalog ) && !empty( $this->arrCatalog ) ? $this->arrCatalog : [];
    }


    public function getRedirectID() {

        return $this->strRedirectID;
    }


    public function getValues() {

        return $this->arrValues;
    }
    
    
    protected function sendJsonResponse( $arrField, $strModuleID, $strKeyword ) {

        $arrField['optionsType'] = 'useActiveDbOptions';
        $arrField['dbColumn'] = $arrField['dbTableKey'];
        $arrField['dbTableValue'] = $arrField['dbTableKey'];

        $objOptionGetter = new OptionsGetter( $arrField, $strModuleID, [ $strKeyword ] );
        $arrWords = array_values( $objOptionGetter->getOptions() );

        header('Content-Type: application/json');

        echo json_encode( [

            'word' => $strKeyword,
            'words' => $arrWords

        ], 12 );

        exit;
    }
}