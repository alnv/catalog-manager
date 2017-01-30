<?php

namespace CatalogManager;

class FrontendEditing extends CatalogController {

    private $Template;
    private $strTable = '';
    private $arrCatalog = [];
    private $blnNoSubmit = false;
    private $arrFormFields = [];
    private $strSubmitName = '';
    private $blnHasUpload = false;

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

        $this->setOptions();

        $strTemplate = $this->arrOptions['catalogFormTemplate'] ? $this->arrOptions['catalogFormTemplate'] : 'form_catalog_default';

        $intIndex = 0;

        $arrFieldsets = [

            'default' => []
        ]; // @todo

        $arrPredefinedDCFields = $this->DCABuilderHelper->getPredefinedDCFields();

        $this->strTable = $strTablename;
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

        if ( !$this->arrOptions['disableCaptcha'] ) {

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
        $objWidget->id = 'id_' . $arrField['_fieldname'];
        $objWidget->storeValues = true;
        $objWidget->rowClass = 'row_' . $intIndex . ( ( $intIndex == 0 ) ? ' row_first' : '' ) . ( ( ( $intIndex % 2 ) == 0 ) ? ' even' : ' odd' );

        if ( $arrField['inputType'] == 'upload' ) {

            //
        }

        if ( $objWidget instanceof \FormPassword ) {

            $objWidget->rowClassConfirm = 'row_' . ++$intIndex . ( ( ( $intIndex % 2 ) == 0 ) ? ' even' : ' odd' );
        }

        if ( \Input::post('FORM_SUBMIT') == $this->strSubmitName ) {

            $objWidget->validate();

            $varValue = $objWidget->value;
            $varValue = $this->decodeValue( $varValue );
            $varValue = $this->replaceInsertTags( $varValue );

            $strRGXP = $arrField['eval']['rgxp'];

            if ( $varValue != '' && in_array( $arrField, [ 'date', 'time', 'datim' ] ) ) {

                try {

                    $objDate = new \Date( $varValue, \Date::getFormatFromRgxp( $strRGXP ) );
                    $varValue = $objDate->tstamp;

                } catch ( \OutOfBoundsException $objError ) {

                    $objWidget->addError( sprintf( $GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue ) );
                }
            }

            if ( $arrField['eval']['unique'] && $varValue != '' && !$this->Database->isUniqueValue( $this->strTable, $arrField['_fieldname'], $varValue ) ) {

                $objWidget->addError( sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $arrField['label'][0] ?: $arrField['_fieldname'] ) );
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

                // …
            }

            $arrFiles = $_SESSION['FILES'];

            if ( !empty( $arrFiles ) && isset( $arrFiles[ $arrField['_fieldname'] ] ) && $this->arrOptions['storeFile'] ) {

                $strRoot = TL_ROOT . '/';
                $strUuid = $arrFiles[ $arrField['_fieldname'] ]['uuid'];
                $strFile = substr( $arrFiles[ $arrField['_fieldname'] ]['tmp_name'], strlen( $strRoot ) );
                $arrFiles = \FilesModel::findByPath($strFile);

                if ($arrFiles !== null) {

                    $strUuid = $arrFiles->uuid;
                }

                // …
            }

            if ( !empty( $arrFiles ) && isset( $arrFiles[ $arrField['_fieldname'] ] ) ) {

                unset( $_SESSION['FILES'][ $arrField['_fieldname'] ] );
            }
        }

        if ($objWidget instanceof \uploadable) {

            $this->blnHasUpload = true;
        }

        $this->Template->fields .= $objWidget->parse();
    }

    public function createDCField( $arrField, $strFieldname ) {

        if ( !$this->DCABuilderHelper->isValidField( $arrField ) ) return null;

        $arrDCField = $this->DCABuilderHelper->convertCatalogField2DCA( $arrField );
        $arrDCField['_fieldname'] = $strFieldname;

        return $arrDCField;
    }

    protected function setOptions() {

        if ( !empty( $this->arrOptions ) && is_array( $this->arrOptions ) ) {

            foreach ( $this->arrOptions as $strKey => $varValue ) {

                $this->{$strKey} = $varValue;
            }
        }
    }

    protected function decodeValue( $varValue ) {

        if (class_exists('StringUtil')) {

            $varValue = \StringUtil::decodeEntities( $varValue );
        }

        else {

            $varValue = \Input::decodeEntities( $varValue );
        }

        return $varValue;
    }

    protected function getCaptcha() {

        $arrCaptcha = [

            'label' => $GLOBALS['TL_LANG']['MSC']['securityQuestion'],
            'id' => 'id_',
            'type' => 'captcha',
            'mandatory' => true,
            'required' => true,
            'tableless' => $this->arrOptions['tableless']
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

        $arrField['eval']['tableless'] = $this->arrOptions['tableless'];
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