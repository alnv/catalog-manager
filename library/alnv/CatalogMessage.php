<?php

namespace CatalogManager;

class CatalogMessage extends CatalogController {


    protected $strTemplate = 'ctlg_message_default';


    public function __construct() {

        parent::__construct();

        $this->import( 'Input' );
    }

    
    public function set( $strType, $arrData = [], $strID ) {

        $strMessage = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'][ $strType ] ? $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'][ $strType ] : $strType;
        $objTemplate = new \FrontendTemplate( $this->strTemplate );

        $arrTemplate = [

            'message' => $strMessage,
            'data' => $arrData
        ];

        $objTemplate->setData( $arrTemplate );
        $strMessageTemplate = $objTemplate->parse();

        \System::setCookie( 'ctlg_FEE_Message' . ( $strID ? $strID : '' ) , $strMessageTemplate, time() + 1000  );
    }


    public function get( $strID = '' ) {

        $strCookieName = 'ctlg_FEE_Message' . ( $strID ? $strID : '' );
        $strMessage = $this->Input->cookie( $strCookieName );

        if ( !$strMessage ) $strMessage = '';

        unset( $_COOKIE[ $strCookieName ] );

        \System::setCookie( $strCookieName, '', -1 );

        return \StringUtil::decodeEntities( $strMessage );
    }
}