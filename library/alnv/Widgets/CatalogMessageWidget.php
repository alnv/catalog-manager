<?php

namespace CatalogManager;

class CatalogMessageWidget extends \Widget {


    protected $strTemplate = 'be_widget';


    public function generate() {

        return $this->ctlgMessage;
    }
}