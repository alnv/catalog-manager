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


    public function sendAjaxResponse( $arrAttributes ) {

        \Input::setGet( '_doNotTriggerAjax', '1' );

        if ( \Input::post('action') == 'catalogFineUploader' ) {
            
            $objWidget = new $GLOBALS['TL_FFL']['catalogFineUploader']( $arrAttributes );
            $objWidget->bnyUploadFolder = $arrAttributes['configAttributes']['uploadFolder'];
            $objWidget->blnStoreFile = $arrAttributes['configAttributes']['storeFile'] ? true : false;
            $objWidget->blnUseHomeDir = $arrAttributes['configAttributes']['useHomeDir'] ? true : false;
            $objWidget->blnDoNotOverwrite = $arrAttributes['configAttributes']['doNotOverwrite'] ? true : false;

            $arrResponse = $objWidget->upload();

            header( 'Content-Type: application/json' );
            echo json_encode( $arrResponse );
            exit;
        }
    }
}