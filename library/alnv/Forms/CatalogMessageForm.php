<?php

namespace CatalogManager;

class CatalogMessageForm extends \Widget {

    
    protected $strTemplate = 'ctlg_form_message';
    protected $strPrefix = 'widget widget-message';


    public function validate() {

        return null;
    }


    public function generate() {

        return $this->ctlgMessage ? $this->ctlgMessage : '';
    }
}