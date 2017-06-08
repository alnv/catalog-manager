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

        $arrField['multiple'] = $arrField['multiple'] ? 'multiple' : '';
        $objTemplate->setData( $arrField );

        return $objTemplate->parse();
    }
}