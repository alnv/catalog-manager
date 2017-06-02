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
        $strTemplate = $this->arrTemplateMap[ $arrField['type'] ];

        if ( !$strTemplate ) return $strReturn;

        $objTemplate = new \FrontendTemplate( $strTemplate );
        $objTemplate->setData( $arrField );

        return $objTemplate->parse();
    }
}