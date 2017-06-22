<?php

namespace CatalogManager;

class ContentCatalogFilterForm extends \ContentElement {


    protected $arrForm = [];
    protected $arrFormFields = [];
    protected $strTemplate = 'ce_catalog_filterform';


    private $arrTemplateMap = [
      
        'text' => 'ctlg_form_field_text',
        'radio' => 'ctlg_form_field_radio',
        'range' => 'ctlg_form_field_range',
        'select' => 'ctlg_form_field_select',
        'checkbox' => 'ctlg_form_field_checkbox',
    ];

    
    protected function compile() {

        $arrFields = [];
        $blnReady = $this->initialize();

        if ( !$blnReady ) return;

        if ( !empty( $this->arrFormFields ) && is_array( $this->arrFormFields ) ) {

            foreach ( $this->arrFormFields as $strName => $arrField ) {

                $this->arrFormFields[$strName] = $this->parseField( $arrField );

                if ( $this->arrFormFields[$strName]['dependOnField'] &&  !$this->getInput( $this->arrFormFields[$strName]['dependOnField'] ) ) {

                    continue;
                }

                $strTemplate = $arrField['template'] ? $arrField['template'] : $this->arrTemplateMap[ $arrField['type'] ];
                if ( !$strTemplate ) continue;

                $objTemplate = new \FrontendTemplate( $strTemplate );
                $objTemplate->setData( $this->arrFormFields[$strName] );

                $arrFields[$strName] = $objTemplate->parse();
            }
        }

        $arrAttributes = Toolkit::deserialize( $this->arrForm['attributes'] );
        $this->arrForm['formID'] = $arrAttributes[0] ? $arrAttributes[0] : 'id_form_' . $this->id;

        $this->Template->fields = $arrFields;
        $this->Template->reset = $this->getResetLink();
        $this->Template->action = $this->getActionAttr();
        $this->Template->method = $this->getMethodAttr();
        $this->Template->cssClass = $arrAttributes[1] ? $arrAttributes[1] : '';
        $this->Template->formID = sprintf( 'id="%s"', $this->arrForm['formID'] );

        if ( $this->arrForm['sendJsonHeader'] ) {

            $this->import( 'CatalogAjaxController' );

            $this->CatalogAjaxController->setData([

                'form' => $this->arrForm,
                'data' => $this->arrFormFields,
                'reset' => $this->Template->reset,
                'fields' => $this->Template->fields,
                'action' => $this->Template->action,
                'method' => $this->Template->method,
                'formID' => $this->Template->formID,
                'cssClass' => $this->Template->cssClass,
            ]);

            $this->CatalogAjaxController->setType( $this->arrForm['sendJsonHeader'] );
            $this->CatalogAjaxController->setModuleID( $this->arrForm['id'] );
            $this->CatalogAjaxController->sendJsonData();
        }
    }

 
    protected function getActionAttr() {

        if ( $this->arrForm['jumpTo'] ) {

            $objPage = new \PageModel();
            $arrPage = $objPage->findPublishedById( $this->arrForm['jumpTo'] );

            if ( $arrPage != null ) {

                return $this->generateFrontendUrl( $arrPage->row() );
            }
        }

        return ampersand( \Environment::get('indexFreeRequest') );
    }


    protected function getMethodAttr() {

        return $this->arrForm['method'] ? $this->arrForm['method'] : 'GET';
    }


    public function getResetLink() {

        if ( !$this->arrForm['resetForm'] ) return '';

        return sprintf( '<a href="%s" id="id_form_%s">'. $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['resetForm'] .'</a>', str_replace( '?' . \Environment::get( 'queryString' ), '', \Environment::get( 'requestUri' ) ), $this->id );
    }


    protected function initialize() {

        if ( !$this->catalogForm ) return false;

        $objForm = $this->Database->prepare('SELECT * FROM tl_catalog_form WHERE id = ?')->limit(1)->execute( $this->catalogForm );

        if ( $objForm->numRows ) {

            $this->arrForm = $objForm->row();
            $this->getFormFieldsByParentID( $objForm->id );

            return true;
        }

        return false;
    }


    protected function getFormFieldsByParentID( $strPID ) {

        $objFields = $this->Database->prepare('SELECT * FROM tl_catalog_form_fields WHERE pid = ? AND invisible != "1" ORDER BY sorting')->execute( $strPID );

        if ( $objFields->numRows ) {

            while ( $objFields->next() ) {

                if ( !$objFields->name ) continue;

                $this->arrFormFields[ $objFields->name ] = $objFields->row();
            }
        }
    }


    protected function parseField( $arrField ) {

        if ( $arrField['type'] == 'range' ) {

            $arrField['gtName'] = $arrField['name'] . '_gt';
            $arrField['ltName'] = $arrField['name'] . '_lt';
        }

        $arrField['multiple'] = $arrField['multiple'] ? 'multiple' : '';
        
        if ( in_array( $arrField['type'], [ 'select', 'radio', 'checkbox' ] ) ) {

            $arrField['options'] = $this->setOptions( $arrField );
        }

        $arrField['value'] = $this->getActiveOptions( $arrField );
        $arrField['cssID'] = Toolkit::deserialize( $arrField['cssID'] );
        $arrField['cssClass'] = $arrField['cssID'][1] ? $arrField['cssID'][1] . ' ' : '';
        $arrField['onchange'] = $arrField['submitOnChange'] ? 'onchange="this.form.submit()"' : '';
        $arrField['fieldID'] = $arrField['cssID'][0] ? sprintf( 'id="%s"', $arrField['cssID'][0] ) : '';
        $arrField['tabindex'] = $arrField['tabindex'] ? sprintf( 'tabindex="%s"', $arrField['tabindex'] ) : '' ;

        return $arrField;
    }


    protected function setOptions( $arrField ) {

        $objOptionGetter = new OptionsGetter( $arrField );

        return $objOptionGetter->getOptions();
    }


    protected function getActiveOptions( $arrField ) {

        $strValue = $arrField['defaultValue'] ? $arrField['defaultValue'] : '';

        if ( $strValue ) \Input::setGet( $arrField['name'], $strValue );

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

        return $strValue;
    }


    protected function getInput( $strFieldname, $strDefault = '' ) {

        if ( !$strFieldname ) return $strDefault;

        if ( \Input::get( $strFieldname ) ) {

           return \Input::get( $strFieldname );
        }

        if ( \Input::post( $strFieldname ) ) {

            return \Input::post( $strFieldname );
        }

        return $strDefault;
    }
}