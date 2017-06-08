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

        $blnReady = $this->initialize();

        if ( !$blnReady ) return;

        $arrFields = [];

        if ( !empty( $this->arrFormFields ) && is_array( $this->arrFormFields ) ) {

            foreach ( $this->arrFormFields as $strName => $arrField ) {

                $arrFields[ $strName ] = $this->parseFieldTemplate( $arrField );
            }
        }

        $this->Template->fields = $arrFields;
        $this->Template->reset = $this->setReset();
        $this->Template->action = $this->setAction();
    }


    protected function setAction() {

        if ( $this->arrForm['jumpTo'] ) {

            $objPage = new \PageModel();
            $arrPage = $objPage->findPublishedById( $this->arrForm['jumpTo'] );

            if ( $arrPage != null ) {

                return $this->generateFrontendUrl( $arrPage->row() );
            }
        }

        return ampersand( \Environment::get('indexFreeRequest') );
    }


    public function setReset() {

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


    protected function parseFieldTemplate( $arrField ) {

        $strReturn = '';
        $strTemplate = $arrField['template'] ? $arrField['template'] : $this->arrTemplateMap[ $arrField['type'] ];

        if ( !$strTemplate ) return $strReturn;

        $objTemplate = new \FrontendTemplate( $strTemplate );

        if ( $arrField['type'] == 'range' ) {

            $arrField['gtName'] = $arrField['name'] . '_gt';
            $arrField['ltName'] = $arrField['name'] . '_lt';
        }

        $arrField['multiple'] = $arrField['multiple'] ? 'multiple' : '';
        
        if ( in_array( $arrField['type'], [ 'select', 'radio', 'checkbox' ] ) ) {

            $arrField['options'] = $this->setOptions( $arrField );
        }

        $arrField['value'] = $this->getActiveOptions( $arrField );

        $objTemplate->setData( $arrField );

        return $objTemplate->parse();
    }


    protected function setOptions( $arrField ) {

        $objOptionGetter = new OptionsGetter( $arrField );

        return $objOptionGetter->getOptions();
    }


    protected function getActiveOptions( $arrField ) {

        $strValue = $arrField['defaultValue'] ? $arrField['defaultValue'] : '';
        $strValue = \Input::get( $arrField['name'] ) ? \Input::get( $arrField['name'] ) : $strValue;

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

                'ltValue' => \Input::get( $arrField['name'] . '_lt' ) ? \Input::get( $arrField['name'] . '_lt' ) : '',
                'gtValue' => \Input::get( $arrField['name'] . '_gt' ) ? \Input::get( $arrField['name'] . '_gt' ) : ''
            ];
        }

        return $strValue;
    }
}