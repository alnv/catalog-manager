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

    private $arrOptions = [

        'tableless' => true,
        'disableCaptcha' => false,
        'formTemplate' => 'form_catalog_default'
    ];

    public function __construct() {

        parent::__construct();

        $this->import( 'SQLQueryBuilder' );
        $this->import( 'SQLHelperQueries' );
        $this->import( 'DCABuilderHelper' );
    }

    public function getCatalogByTablename( $strTablename ) {

        return $this->SQLHelperQueries->getCatalogByTablename( $strTablename );
    }

    public function getCatalogFieldsByCatalogID( $strID ) {

        return $this->SQLHelperQueries->getCatalogFieldsByCatalogID( $strID, [ 'FrontendEditing', 'createDCField' ] );
    }

    public function getCatalogFormByTablename( $strTablename, $arrOptions = [] ) {

        $intIndex = 0;
        $arrFieldsets = [ 'default' => [] ];
        $this->strTable = $strTablename;
        $this->strSubmitName = 'submit_' . $this->strTable;

        if ( !$this->SQLQueryBuilder->tableExist( $this->strTable ) ) return 'table do not exist.';

        $this->setOptions( $arrOptions );

        \System::loadLanguageFile('catalog_manager');

        $this->Template = new \FrontendTemplate( $this->arrOptions['formTemplate'] );

        $arrPredefinedDCFields = $this->DCABuilderHelper->getPredefinedDCFields();

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
        $this->Template->submitName = $this->strSubmitName;
        $this->Template->tableless = $this->arrOptions['tableless'];
        $this->Template->enctype = $this->blnHasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';

        return $this->Template->parse();
    }

    public function createAndValidateForm( $arrField, $intIndex ) {

        $strClass = $this->fieldClassExist( $arrField['inputType'] );

        if ( $strClass == false ) return null;

        $objWidget = new $strClass( $strClass::getAttributesFromDca( $arrField, $arrField['_fieldname'], $arrField['default'], '', '', $this ) );
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

                //
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

    public function createDCField( $arrField, $intIndex, $intCount, $strFieldname ) {

        if ( !$this->DCABuilderHelper->isValidField( $arrField ) ) return null;

        $arrDCField = $this->DCABuilderHelper->convertCatalogField2DCA( $arrField );

        $arrDCField['_fieldname'] = $strFieldname;

        return $this->convertWidgetToField( $arrDCField );
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

    protected function setOptions( $arrOptions ) {

        if ( !empty( $arrOptions ) && is_array( $arrOptions ) ) {

            foreach ( $arrOptions as $strKey => $strValue ) {

                $this->arrOptions[ $strKey ] = $strValue;
            }
        }
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

        $arrField['eval']['tableless'] = '';
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