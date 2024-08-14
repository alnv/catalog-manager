<?php

namespace Alnv\CatalogManagerBundle\Widgets;

class CatalogMessageWidget extends \Widget {


    protected $strTemplate = 'be_widget';


    public function generate() {

        return $this->ctlgMessage;
    }
}