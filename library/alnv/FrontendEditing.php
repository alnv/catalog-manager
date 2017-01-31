<?php

namespace CatalogManager;

class FrontendEditing extends CatalogController {

    private $Template;
    private $strTable = '';
    private $arrValues = [];
    private $arrCatalog = [];
    private $blnNoSubmit = false;
    private $arrFormFields = [];
    private $strSubmitName = '';
    private $blnHasUpload = false;

    public $strItemID;
    public $arrOptions = [];

    public function __construct() {

        parent::__construct();

        $this->import( 'SQLQueryHelper' );
        $this->import( 'SQLQueryBuilder' );
        $this->import( 'DCABuilderHelper' );
    }

    public function getCatalogByTablename( $strTablename ) {

        return $this->SQLQueryHelper->getCatalogByTablename( $strTablename );
    }

    public function getCatalogFieldsByCatalogID( $strID ) {

        return $this->SQLQueryHelper->getCatalogFieldsByCatalogID( $strID, [ 'FrontendEditing', 'createDCField' ] );
    }

    public function getCatalogFormByTablename( $strTablename ) {

        if ( !$this->SQLQueryBuilder->tableExist( $strTablename ) ) return 'table do not exist.';

        \System::loadLanguageFile('catalog_manager');

        $this->strTable = $strTablename;

        $this->setOptions();
        $this->setValues();

        $strTemplate = $this->catalogFormTemplate ? $this->catalogFormTemplate : 'form_catalog_default';

        $intIndex = 0;

        $arrFieldsets = [

            'default' => []
        ]; // @todo

        $arrPredefinedDCFields = $this->DCABuilderHelper->getPredefinedDCFields();

        $this->strSubmitName = 'submit_' . $this->strTable;

        $this->Template = new \FrontendTemplate( $strTemplate );
        $this->Template->setData( $this->arrOptions );

        $this->arrCatalog = $this->getCatalogByTablename( $this->strTable );
        $this->arrFormFields = $this->getCatalogFieldsByCatalogID( $this->arrCatalog['id'] );
        $this->arrFormFields[] = $arrPredefinedDCFields['invisible'];

        unset( $arrPredefinedDCFields['invisible'] );

        array_insert( $this->arrFormFields, 0, $arrPredefinedDCFields );

        if ( !empty( $this->arrFormFields ) && is_array( $this->arrFormFields ) ) {

            foreach ( $this->arrFormFields as $arrField ) {

                $this->createAndValidateForm( $arrField, $intIndex );

                $intIndex++;
            }
        }

        if ( !$this->disableCaptcha ) {

            $objCaptcha = $this->getCaptcha();
            $objCaptcha->rowClass = 'row_' . $intIndex . ( ( $intIndex == 0 ) ? ' row_first' : '' ) . ( ( ( $intIndex % 2 ) == 0 ) ? ' even' : ' odd' );
            $strCaptcha = $objCaptcha->parse();

            $this->Template->fields .= $strCaptcha;
        }

        $this->Template->method = 'POST';
        $this->Template->formId = $this->strSubmitName;
        $this->Template->submitName = $this->strSubmitName;
        $this->Template->action = \Environment::get( 'indexFreeRequest' );
        $this->Template->enctype = $this->blnHasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';

        return $this->Template->parse();
    }

    public function createAndValidateForm( $arrField, $intIndex ) {

        $arrField = $this->convertWidgetToField( $arrField );
        $strClass = $this->fieldClassExist( $arrField['inputType'] );

        if ( $strClass == false ) return null;

        $objWidget = new $strClass( $strClass::getAttributesFromDca( $arrField, $arrField['_fieldname'], $arrField['default'], '', '', $this ) );

        $objWidget->storeValues = true;
        $objWidget->id = 'id_' . $arrField['_fieldname'];
        $objWidget->value = $this->arrValues[ $arrField['_fieldname'] ];
        $objWidget->rowClass = 'row_' . $intIndex . ( ( $intIndex == 0 ) ? ' row_first' : '' ) . ( ( ( $intIndex % 2 ) == 0 ) ? ' even' : ' odd' );

        if ( $arrField['inputType'] == 'upload' ) {

            if ( !$this->catalogStoreFile ) return null;

            $objWidget->storeFile = $this->catalogStoreFile;
            $objWidget->useHomeDir = $this->catalogUseHomeDir;
            $objWidget->uploadFolder = $this->catalogUploadFolder;
            $objWidget->doNotOverwrite = $this->catalogDoNotOverwrite;
            $objWidget->extensions = $arrField['eval']['extensions'];
            $objWidget->maxlength = $arrField['eval']['maxsize'];
            
            $this->blnHasUpload = true;
        }

        if ( $objWidget instanceof \FormPassword ) {

            $objWidget->rowClassConfirm = 'row_' . ++$intIndex . ( ( ( $intIndex % 2 ) == 0 ) ? ' even' : ' odd' );
        }

        if ( \Input::post('FORM_SUBMIT') == $this->strSubmitName ) {

            $objWidget->validate();

            $varValue = $objWidget->value;

            if ( $varValue && is_string( $varValue ) ) {

                $varValue = $this->decodeValue( $varValue );
                $varValue = $this->replaceInsertTags( $varValue );
            }

            $strRGXP = $arrField['eval']['rgxp'];

            if ( $varValue != '' && in_array( $arrField, [ 'date', 'time', 'datim' ] ) ) {

                try {

                    $objDate = new \Date( $varValue, \Date::getFormatFromRgxp( $strRGXP ) );
                    $varValue = $objDate->tstamp;

                } catch ( \OutOfBoundsException $objError ) {

                    $objWidget->addError( sprintf( $GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue ) );
                }
            }

            if ( $arrField['eval']['unique'] && $varValue != '' && !$this->SQLQueryHelper->SQLQueryBuilder->Database->isUniqueValue( $this->strTable, $arrField['_fieldname'], $varValue ) ) {

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

                    catch  (\Exception $objError ) {

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

                $this->arrValues[ $arrField['_fieldname'] ] = $varValue;
            }

            $arrFiles = $_SESSION['FILES'];

            if ( !empty( $arrFiles ) && isset( $arrFiles[ $arrField['_fieldname'] ] ) && $this->catalogStoreFile ) {

                $strRoot = TL_ROOT . '/';
                $strUuid = $arrFiles[ $arrField['_fieldname'] ]['uuid'];
                $strFile = substr( $arrFiles[ $arrField['_fieldname'] ]['tmp_name'], strlen( $strRoot ) );
                $arrFiles = \FilesModel::findByPath( $strFile );

                if ($arrFiles !== null) {

                    $strUuid = $arrFiles->uuid;
                }

                $this->arrValues[ $arrField['_fieldname'] ] = $strUuid;
            }

            if ( !empty( $arrFiles ) && isset( $arrFiles[ $arrField['_fieldname'] ] ) ) {

                unset( $_SESSION['FILES'][ $arrField['_fieldname'] ] );
            }
        }

        $this->Template->fields .= $objWidget->parse();
    }

    public function createDCField( $arrField, $strFieldname ) {

        if ( !$this->DCABuilderHelper->isValidField( $arrField ) ) return null;

        $arrDCField = $this->DCABuilderHelper->convertCatalogField2DCA( $arrField );
        $arrDCField['_fieldname'] = $strFieldname;

        return $arrDCField;
    }

    private function setValues() {

        if ( $this->strItemID ) {
            
            $this->arrValues = $this->SQLQueryHelper->getCatalogTableItemByID( $this->strTable, $this->strItemID );
        }

        if ( !empty( $this->arrValues ) && is_array( $this->arrValues ) ) {

            foreach ( $this->arrValues as $strKey => $varValue ) {

                $this->arrValues[$strKey] = \Input::post( $strKey ) ? \Input::post( $strKey ) : $varValue;
            }
        }
    }

    private function setOptions() {

        if ( !empty( $this->arrOptions ) && is_array( $this->arrOptions ) ) {

            foreach ( $this->arrOptions as $strKey => $varValue ) {

                $this->{$strKey} = $varValue;
            }
        }
    }

    private function decodeValue( $varValue ) {

        if ( class_exists('StringUtil') ) {

            $varValue = \StringUtil::decodeEntities( $varValue );
        }

        else {

            $varValue = \Input::decodeEntities( $varValue );
        }

        return $varValue;
    }

    private function getCaptcha() {

        $arrCaptcha = [

            'label' => $GLOBALS['TL_LANG']['MSC']['securityQuestion'],
            'id' => 'id_',
            'type' => 'captcha',
            'mandatory' => true,
            'required' => true,
            'tableless' => $this->tableless
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

    private function convertWidgetToField( $arrField ) {

        if ( $arrField['inputType'] == 'checkboxWizard' ) {

            $arrField['inputType'] = 'checkbox';
        }

        if ( $arrField['inputType'] == 'fileTree' ) {

            $arrField['inputType'] = 'upload';
        }

        $arrField['eval']['tableless'] = $this->tableless;
        $arrField['eval']['required'] = $arrField['eval']['mandatory'];

        return $arrField;
    }

    private function fieldClassExist( $strInputType ) {

        $strClass = $GLOBALS['TL_FFL'][$strInputType];

        if ( !class_exists( $strClass ) ) {

            return false;
        }

        return $strClass;
    }
}