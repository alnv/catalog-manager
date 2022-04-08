<?php

namespace CatalogManager;


class CatalogFormFilter extends CatalogController {


    protected $arrForm = [];
    protected $strFormId = null;
    protected $blnReady = false;
    protected $blnIsValid = true;
    protected $arrFormFields = [];
    protected $arrTemplateMap = [
        'text' => 'ctlg_form_field_text',
        'radio' => 'ctlg_form_field_radio',
        'range' => 'ctlg_form_field_range',
        'select' => 'ctlg_form_field_select',
        'checkbox' => 'ctlg_form_field_checkbox',
    ];
    protected $strTemplate = 'ce_catalog_filterform';


    public function __construct( $strId ) {

        parent::__construct();

        $this->import('Database');
        $this->import('CatalogInput');

        $this->strFormId = $strId;
        $this->initialize();
    }


    public function render( $objFormTemplate = null, $strCustomTemplate = 'ctlg_inserttag_filterform' ) {

        if ( !$this->blnReady ) {

            return '';
        }

        if ( !$objFormTemplate ) {

            $objFormTemplate = new \FrontendTemplate( $strCustomTemplate );
        }

        $arrFields = [];
        $arrParameters = [];
        $strAction = $this->getActionAttr();
        $strFormId = md5( $this->strFormId );

        if ( !empty( $this->arrFormFields ) && is_array( $this->arrFormFields ) ) {

            foreach ( $this->arrFormFields as $strName => $arrField ) {

                $this->arrFormFields[ $strName ] = $this->parseField( $arrField );

                if ( $this->arrFormFields[ $strName ]['type'] == 'hidden' && $this->arrFormFields[ $strName ]['name'] ) {

                    $arrFields[ $strName ] = sprintf( '<input type="hidden" name="%s" value="%s">',

                        $this->arrFormFields[ $strName ]['name'],
                        $this->arrFormFields[ $strName ]['value'] ? $this->arrFormFields[ $strName ]['value'] : $this->arrFormFields[ $strName ]['defaultValue']
                    );

                    continue;
                }

                if ( $this->arrFormFields[ $strName ]['dependOnField'] ) {

                    if ( !$this->validValue( $this->getInput( $this->arrFormFields[ $strName ]['dependOnField'] ) ) ) {

                        if ( $this->validValue( $this->getInput( $strName ) ) ) {

                            \Controller::redirect( $strAction );
                        }

                        continue;
                    }
                }

                if ( $this->arrFormFields[ $strName ]['requiredOptions'] && in_array( $this->arrFormFields[ $strName ]['type'], [ 'select', 'radio', 'checkbox' ] ) && empty( $this->arrFormFields[ $strName ]['options'] ) ) {

                    if ( $this->validValue( $this->getInput( $strName ) ) ) {

                        $arrParameters[] = $strName;
                    }

                    continue;
                }

                if ( in_array( $this->arrFormFields[ $strName ]['type'], [ 'select', 'radio', 'checkbox' ] ) && !empty( $this->arrFormFields[ $strName ]['options'] ) ) {

                    if ( $this->validValue( $this->getInput( $strName ) ) ) {

                        $arrOptions = array_keys( $this->arrFormFields[ $strName ]['options'] );
                        $arrActiveValues = $this->getInput( $strName );

                        if ( !is_array( $arrActiveValues ) ) {

                            $arrActiveValues = [ $arrActiveValues ];
                        }

                        if ( !array_intersect( $arrActiveValues, $arrOptions ) ) {

                            $arrParameters[] = $strName;
                        }
                    }
                }

                $strTemplate = $arrField['template'] ? $arrField['template'] : $this->arrTemplateMap[$arrField['type']];
                if (!$strTemplate) {
                    $strTemplate = 'ctlg_form_field_' . $arrField['type'];
                }

                if ( $this->getInput( '_submit' ) == $strFormId ) {

                    if ( $this->arrFormFields[ $strName ]['mandatory'] && !$this->validValue( $this->arrFormFields[ $strName ]['value'], $this->arrFormFields[ $strName ]['type'] ) ) {

                        $this->blnIsValid = false;
                        $this->arrFormFields[ $strName ]['invalid'] = true;
                        $this->arrFormFields[ $strName ]['cssClass'] .= 'error ';
                        $this->arrFormFields[ $strName ]['description'] = sprintf( $GLOBALS['TL_LANG']['ERR']['mandatory'], $this->arrFormFields[ $strName ]['title'] );
                    }

                    if ($this->arrFormFields[$strName]['value'] && $this->arrFormFields[$strName]['rgxp'] && $this->arrFormFields[$strName]['type'] == 'text' && !$this->validate($this->arrFormFields[$strName]['value'], $this->arrFormFields[$strName]['rgxp'])) {
                        $this->blnIsValid = false;
                        $this->arrFormFields[$strName]['value'] = '';
                        $this->arrFormFields[$strName]['invalid'] = true;
                        $this->arrFormFields[$strName]['cssClass'] .= 'error ';
                        $this->arrFormFields[$strName]['description'] = sprintf($GLOBALS['TL_LANG']['ERR'][$this->arrFormFields[$strName]['rgxp']], $this->arrFormFields[$strName]['dateFormat']);
                    }
                }

                if (isset($GLOBALS['TL_HOOKS']['catalogManagerFormFieldsFilter']) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerFormFieldsFilter'])) {
                    foreach ($GLOBALS['TL_HOOKS']['catalogManagerFormFieldsFilter'] as $arrCallback) {
                        if (is_array($arrCallback)) {
                            $this->import( $arrCallback[0] );
                            $this->{$arrCallback[0]}->{$arrCallback[1]}($strName, $this->arrFormFields[$strName], $this->arrFormFields, $this->arrForm, $this);
                        }
                    }
                }

                $objTemplate = new \FrontendTemplate($strTemplate);
                $objTemplate->setData($this->arrFormFields[$strName]);

                $arrFields[$strName] = $objTemplate->parse();
            }
        }

        $arrAttributes = Toolkit::deserialize( $this->arrForm['attributes'] );

        $strCssClass = ( !empty( $arrParameters ) ? 'filter_reloading ' : '' );
        $strCssClass .= $arrAttributes[1] ? $arrAttributes[1] : '';
        $arrSubmitAttributes = Toolkit::deserialize( $this->arrForm['submitAttributes'] );

        $this->arrForm['formID'] = $arrAttributes[0] ? $arrAttributes[0] : 'id_form_' . $this->strFormId;

        $objFormTemplate->submitId = '';
        $objFormTemplate->attributes = '';
        $objFormTemplate->fields = $arrFields;
        $objFormTemplate->formId = $strFormId;
        $objFormTemplate->action = $strAction;
        $objFormTemplate->cssClass = $strCssClass;
        $objFormTemplate->triggerId = $this->strFormId;
        $objFormTemplate->reset = $this->getResetLink();
        $objFormTemplate->method = $this->getMethodAttr();
        $objFormTemplate->trigger = !empty( $arrParameters );
        $objFormTemplate->formSubmit = md5( 'tl_filter' );
        $objFormTemplate->disableSubmit = $this->arrForm['disableSubmit'];
        $objFormTemplate->formID = sprintf( 'id="%s"', $this->arrForm['formID'] );
        $objFormTemplate->submit = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['filter'];
        $objFormTemplate->submitCssClass = isset( $arrSubmitAttributes[1] ) && $arrSubmitAttributes[1] ? ' ' . $arrSubmitAttributes[1] : '';

        if ( isset( $arrSubmitAttributes[0] ) && $arrSubmitAttributes[0] ) $objFormTemplate->submitId = sprintf( 'id="%s"', $arrSubmitAttributes[0] );

        if ( $this->arrForm['disableHtml5Validation'] ) $objFormTemplate->attributes .= 'novalidate';

        if ( $this->arrForm['sendJsonHeader'] ) {

            $this->import( 'CatalogAjaxController' );

            $this->CatalogAjaxController->setData([
                'form' => $this->arrForm,
                'data' => $this->arrFormFields,
                'reset' => $objFormTemplate->reset,
                'fields' => $objFormTemplate->fields,
                'action' => $objFormTemplate->action,
                'method' => $objFormTemplate->method,
                'formID' => $objFormTemplate->formID,
                'cssClass' => $objFormTemplate->cssClass,
            ]);

            $this->CatalogAjaxController->setType( $this->arrForm['sendJsonHeader'] );
            $this->CatalogAjaxController->setModuleID( $this->arrForm['id'] );
            $this->CatalogAjaxController->sendJsonData();
        }

        return $objFormTemplate->parse();
    }


    public function parseField( $arrField ) {

        if ( $arrField['type'] == 'range' ) {

            $arrField['gtName'] = $arrField['name'] . '_gt';
            $arrField['ltName'] = $arrField['name'] . '_lt';
        }

        $arrField['multiple'] = $arrField['multiple'] ? 'multiple' : '';

        if ( in_array( $arrField['type'], [ 'select', 'radio', 'checkbox' ] ) ) {

            if ( $arrField['dbParseDate'] ) {

                $arrField['dbParseDate'] = true;
                $arrField['dbDateFormat'] = $arrField['dbDateFormat'] ? $arrField['dbDateFormat'] : 'monthBegin';
            }

            $objOptionGetter = new OptionsGetter( $arrField );
            $arrField['options'] = $objOptionGetter->getOptions();
        }

        if ( $arrField['type'] == 'text' && $arrField['rgxp'] && in_array( $arrField['rgxp'], [ 'date', 'time', 'dateTime' ] ) ) {

            global $objPage;

            $strFormat = ( $arrField['rgxp'] == 'dateTime' ? 'datim' : $arrField['rgxp'] ) . 'Format';

            $arrField['dateFormat'] = $objPage->{$strFormat};
        }

        $arrField['message'] = '';
        $arrField['attributes'] = '';
        $arrField['invalid'] = false;
        $arrField['fieldCssClass'] = $arrField['type'];
        $arrField['value'] = $this->getActiveOptions( $arrField );
        $arrField['cssID'] = Toolkit::deserialize( $arrField['cssID'] );
        $arrField['cssClass'] = $arrField['cssID'][1] ? $arrField['cssID'][1] . ' ' : '';
        $arrField['onchange'] = $arrField['submitOnChange'] ? 'onchange="this.form.submit()"' : '';
        $arrField['fieldID'] = $arrField['cssID'][0] ? sprintf( 'id="%s"', $arrField['cssID'][0] ) : '';
        $arrField['tabindex'] = $arrField['tabindex'] ? sprintf( 'tabindex="%s"', $arrField['tabindex'] ) : '' ;

        if ( $arrField['mandatory'] ) $arrField['attributes'] .= ' required';

        if ( $arrField['type'] == 'text' && $arrField['autoCompletionType'] ) {

            $arrField['fieldCssClass'] .= ' awesomplete-field' . ( $arrField['multiple'] ? ' multiple' : '' );

            if ( \Input::get('ctlg_autocomplete_query') && \Input::get('ctlg_fieldname') == $arrField['name'] ) {

                $this->sendJsonResponse( $arrField, \Input::get('ctlg_autocomplete_query') );
            }

            $objScriptLoader = new CatalogScriptLoader();
            $objScriptLoader->loadScript('awesomplete-frontend' );
            $objScriptLoader->loadStyle('awesomplete' );
        }

        if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerModifyFilterField'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerModifyFilterField'] ) ) {

            foreach ( $GLOBALS['TL_HOOKS']['catalogManagerModifyFilterField'] as $callback ) {

                $this->import( $callback[0] );
                $arrField = $this->{$callback[0]}->{$callback[1]}( $arrField, $this );
            }
        }

        return $arrField;
    }


    protected function initialize() {

        $objForm = $this->Database->prepare('SELECT * FROM tl_catalog_form WHERE id = ?')->limit(1)->execute( $this->strFormId );

        if ( $objForm->numRows ) {

            $this->arrForm = $objForm->row();
            $this->getFormFields( $objForm->id );

            $this->blnReady = true;
        }
    }


    protected function getFormFields( $strPID ) {

        $objFields = $this->Database->prepare('SELECT * FROM tl_catalog_form_fields WHERE pid = ? AND invisible != "1" ORDER BY sorting')->execute( $strPID );

        if ( $objFields->numRows ) {

            while ( $objFields->next() ) {

                if ( !$objFields->name ) continue;

                $this->arrFormFields[ $objFields->name ] = $objFields->row();
            }
        }
    }


    protected function getActiveOptions( $arrField ) {

        $strValue = !Toolkit::isEmpty( $arrField['defaultValue'] ) ? \Controller::replaceInsertTags( $arrField['defaultValue'] ) : '';
        $strValue = $this->getInput( $arrField['name'], $strValue );

        if ( $arrField['type'] == 'select' || $arrField['type'] == 'checkbox' ) {

            $arrReturn = [];

            if ( $strValue && ( empty( $arrField['options'] ) || !is_array( $arrField['options'] ) ) ) {

                return $arrReturn;
            }

            if ( !is_array( $strValue ) ) {

                $arrReturn[ $strValue ] = $strValue;

                return $arrReturn;
            }

            return $strValue;
        }

        if ( $arrField['type'] == 'range' ) {

            return [

                'ltValue' => $this->getInput( $arrField['name'] . '_lt' ),
                'gtValue' => $this->getInput( $arrField['name'] . '_gt' )
            ];
        }

        if ( is_array( $strValue ) && $arrField['type'] == 'text' ) {

            return implode( ' ', $strValue );
        }

        return $strValue;
    }


    protected function getInput( $strFieldname, $strDefault = '' ) {

        if ( !$strFieldname ) return $strDefault;

        $strValue = $this->CatalogInput->getActiveValue( $strFieldname );

        if ( Toolkit::isEmpty( $strValue ) ) return $strDefault;

        return $strValue;
    }


    protected function sendJsonResponse( $arrField, $strKeyword ) {

        $arrField['optionsType'] = 'useActiveDbOptions';
        $arrField['dbColumn'] = $arrField['dbTableKey'];
        $arrField['dbTableValue'] = $arrField['dbTableKey'];

        $objOptionGetter = new OptionsGetter( $arrField, null, [ $strKeyword ] );
        $arrWords = array_values( $objOptionGetter->getOptions() );

        header('Content-Type: application/json');

        echo json_encode( [

            'word' => $strKeyword,
            'words' => $arrWords

        ], 12 );

        exit;
    }


    protected function validValue( $varValue, $strType = '' ) {

        if ( is_array( $varValue ) ) $varValue = array_values( $varValue );

        if ( empty( $varValue ) && is_array( $varValue ) ) return false;

        if ( is_array( $varValue ) && count( $varValue ) >= 1 && Toolkit::isEmpty( $varValue[0] ) ) return false;

        if ( $strType == 'range' && Toolkit::isEmpty( $varValue[1] ) ) return false;

        if ( Toolkit::isEmpty( $varValue ) ) return false;

        return true;
    }


    protected function validate( $varValue, $strType ) {

        if ( $strType == 'date' ) return \Validator::isDate( $varValue ) ? true : false;
        if ( $strType == 'time' ) return \Validator::isTime( $varValue ) ? true : false;
        if ( $strType == 'dateTime' ) return \Validator::isDatim( $varValue ) ? true : false;

        return false;
    }


    protected function getActionAttr() {

        if ( !$this->blnIsValid ) return ampersand( \Environment::get('indexFreeRequest') );

        $strAlias = '';
        $strPageID = $this->arrForm['jumpTo'];

        if ( !$strPageID ) {

            global $objPage;

            $strPageID = $objPage->id;
            $strAlias = \Input::get( 'auto_item' ) ? '/' . \Input::get( 'auto_item' ) : '';
        }

        $objPageModel = new \PageModel();
        $arrPage = $objPageModel->findPublishedById( $strPageID );

        if ( $arrPage != null ) return \Controller::generateFrontendUrl( $arrPage->row(), $strAlias ) . ( $this->arrForm['anchor'] ? '#' . $this->arrForm['anchor'] : '' );

        return ampersand( \Environment::get('indexFreeRequest') ) . ( $this->arrForm['anchor'] ? '#' . $this->arrForm['anchor'] : '' );
    }


    protected function getMethodAttr() {

        return $this->arrForm['method'] ? $this->arrForm['method'] : 'GET';
    }


    protected function getResetLink() {

        if ( !$this->arrForm['resetForm'] || $this->arrForm['method'] == 'POST' ) return '';

        $strCurrentUrl = \Environment::get( 'requestUri' );
        $strClearUrl = strtok( $strCurrentUrl, '?' );

        if ( $strClearUrl === false ) {

            $strClearUrl = $strCurrentUrl;
        }

        return sprintf( '<a href="%s" id="id_reset_%s">'. $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['resetForm'] .'</a>',

            $strClearUrl . ( $this->arrForm['anchor'] ? '#' . $this->arrForm['anchor'] : '' ),
            $this->strFormId
        );
    }


    public function getCustomTemplate() {

        return $this->arrForm['template'] ? $this->arrForm['template'] : '';
    }


    public function getState() {

        return $this->blnReady;
    }


    public function disableAutoItem() {

        return ( $this->arrForm['disableOnAutoItem'] && !Toolkit::isEmpty( \Input::get( 'auto_item' ) ) );
    }
}