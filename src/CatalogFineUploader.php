<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Input;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class CatalogFineUploader
{

    protected array $arrOptions = [];

    protected bool $blnAssetsLoaded = false;

    public function loadAssets(): void
    {

        $blnIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));

        if ($blnIsBackend && !$this->blnAssetsLoaded) {

            $GLOBALS['TL_JAVASCRIPT']['catalogFineUploader'] = 'bundles/alnvcatalogmanager/fineUploader/fine-uploader.min.js';
            $GLOBALS['TL_JAVASCRIPT']['catalogFrontendExtension'] = 'bundles/alnvcatalogmanager/FrontendExtension.js';
            $GLOBALS['TL_CSS']['catalogFineUploader'] = 'bundles/alnvcatalogmanager/fineUploader/fine-uploader-new.min.css';

            $this->blnAssetsLoaded = true;
        }
    }

    public function sendAjaxResponse($arrAttributes)
    {

        Input::setGet('_doNotTriggerAjax', '1');

        if (Input::post('action') == 'catalogFineUploader') {

            $objWidget = new $GLOBALS['TL_FFL']['catalogFineUploader']($arrAttributes);
            $objWidget->bnyUploadFolder = $arrAttributes['configAttributes']['uploadFolder'];
            $objWidget->blnStoreFile = (bool)$arrAttributes['configAttributes']['storeFile'];
            $objWidget->blnUseHomeDir = (bool)$arrAttributes['configAttributes']['useHomeDir'];
            $objWidget->blnDoNotOverwrite = (bool)$arrAttributes['configAttributes']['doNotOverwrite'];

            $arrResponse = $objWidget->upload();

            header('Content-Type: application/json');
            echo json_encode($arrResponse);
            exit;
        }
    }
}