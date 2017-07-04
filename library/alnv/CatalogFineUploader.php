<?php

namespace CatalogManager;

class CatalogFineUploader {

    
    protected $arrOptions = [];
    protected $blnAssetsLoaded = false;

    
    public function loadAssets() {
        
        if ( TL_MODE == 'FE' && !$this->blnAssetsLoaded ) {

            $GLOBALS['TL_JAVASCRIPT']['catalogFineUploader'] = 'system/modules/catalog-manager/assets/fineUploader/fine-uploader.min.js';
            $GLOBALS['TL_JAVASCRIPT']['catalogFrontendExtension'] = 'system/modules/catalog-manager/assets/FrontendExtension.js';
            $GLOBALS['TL_CSS']['catalogFineUploader'] = 'system/modules/catalog-manager/assets/fineUploader/fine-uploader-new.min.css';

            $this->blnAssetsLoaded = true;
        }
    }


    public function sendAjaxResponse() {

        // @todo call here fine upload widget

        header('Content-Type: application/json');

        echo json_encode([

            'success' => true
        ]);

        exit;
    }
}