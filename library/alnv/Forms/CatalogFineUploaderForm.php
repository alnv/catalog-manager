<?php

namespace CatalogManager;

class CatalogFineUploaderForm extends \Widget {


    protected $blnSubmitInput = true;
    protected $strTemplate = 'ctlg_form_fine_uploader';
    protected $strPrefix = 'widget widget-fine-uploader';

    
    public function __construct( $arrAttributes = null ) {

        $strPost = \Input::post('name') ? \Input::post('name') : '';
        $strName = 'id_' . $arrAttributes['name'];
        $strID = 'id_' . $arrAttributes['id'];

        if ( \Environment::get('isAjaxRequest') && $strPost && ( $strID === $strPost || $strName === $strPost ) ) {

            $this->import('CatalogFineUploader');
            $this->CatalogFineUploader->sendAjaxResponse();
        }

        parent::__construct( $arrAttributes );
    }


    public function validate() {

       return null;
    }


    public function generate() {}


    public function parse( $arrAttributes = null ) {

        $this->multiple = json_encode( $this->multiple );
        $this->extensions = json_encode( explode( ',', $this->extensions ) );
        $this->maxlength = $this->maxlength ? json_encode( $this->maxlength ) : '0';
        
        return parent::parse( $arrAttributes );
    }
}