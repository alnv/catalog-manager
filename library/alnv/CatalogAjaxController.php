<?php

namespace CatalogManager;

class CatalogAjaxController extends CatalogController {


    protected $strType = '';
    protected $arrData = [];
    protected $strModuleID = '';


    public function __construct() {

        parent::__construct();

        $this->import( 'Environment' );
    }


    public function setModuleID( $strID ) {

        $this->strModuleID = $strID ? $strID : '';
    }


    public function setData( $arrData ) {

        if ( !empty( $arrData ) && is_array( $arrData ) ) {

            $this->arrData = $arrData;
        }
    }


    public function setType( $strType ) {

        $this->strType = $strType ? $strType : '';
    }


    public function sendJsonData() {

        switch ( $this->strType ) {

            case 'permanent':

                header('Content-Type: application/json');
                echo json_encode( $this->arrData );
                exit;

                break;

            case 'onAjaxCall':

                if ( $this->Environment->get( 'isAjaxRequest' ) || ( \Input::get('ctlg_ajax') && \Input::get('ctlg_ajax') == '1' )  ) {

                    if ( \Input::get('ctlg_module') && $this->strModuleID && \Input::get('ctlg_module') != $this->strModuleID ) {

                        return null;
                    }

                    header('Content-Type: application/json');
                    echo json_encode( $this->arrData );
                    exit;
                }

                break;
        }
    }
}