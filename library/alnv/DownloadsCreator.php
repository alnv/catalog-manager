<?php

namespace CatalogManager;

class DownloadsCreator extends \Frontend {


    public $multiSRC = [];


    protected $objFiles = null;


    public function __construct( $arrMultiSRC, $arrGallery ) {

        foreach ( $arrGallery as $strKey => $strValue ) {

            $this->{$strKey} = $strValue;
        }

        if ( !$this->objFiles && is_array( $arrMultiSRC ) ) {

            $this->multiSRC = $arrMultiSRC;
            $this->objFiles = \FilesModel::findMultipleByUuids( $arrMultiSRC );
        }
    }


    public function render() {

        global $objPage;

        return '';
    }
}