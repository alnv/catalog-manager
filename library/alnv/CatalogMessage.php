<?php

namespace CatalogManager;

class CatalogMessage extends CatalogController {


    protected $strTemplate = 'ctlg_message_default';


    public function __construct() {

        parent::__construct();

        $this->import( 'Input' );
    }

    public function set( $strType, $arrData = [] ) {

        $strMessage = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'][ $strType ] ? $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'][ $strType ] : $strType;
        $objTemplate = new \FrontendTemplate( $this->strTemplate );

        $arrTemplate = [

            'message' => $strMessage,
            'data' => $arrData
        ];

        $objTemplate->setData( $arrTemplate );
        $strMessageTemplate = $objTemplate->parse();
        \System::setCookie( 'ctlg_FEE_Message', $strMessageTemplate, time() + 1000  );
    }


    public function get() {

        $strMessage = $this->Input->cookie( 'ctlg_FEE_Message' );
        if ( !$strMessage ) $strMessage = '';
        \System::setCookie( 'ctlg_FEE_Message', '', 0 );
        return \StringUtil::decodeEntities( $strMessage );
    }
}