<?php

namespace CatalogManager;

class CatalogMessage extends CatalogController {

    protected $strTemplate = 'ctlg_message_default';

    public function __construct() {

        parent::__construct();

        $this->import('Input');
    }

    public function set($strType, $arrData=[], $strID='') {

        $strMessage = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'][$strType] ?: $strType;
        $objTemplate = new \FrontendTemplate($this->strTemplate);

        $arrTemplate = [
            'message' => $strMessage,
            'data' => $arrData
        ];

        $objTemplate->setData($arrTemplate);
        $strMessageTemplate = $objTemplate->parse();

        $_SESSION['ctlg_FEE_Message'.($strID ?: '')] = $strMessageTemplate;
    }

    public function get($strID='') {

        $strCookieName = 'ctlg_FEE_Message' . ($strID ?: '');
        $strMessage = $_SESSION[$strCookieName] ?? '';

        if (!$strMessage) $strMessage = '';
        unset($_SESSION[$strCookieName]);

        return \StringUtil::decodeEntities($strMessage);
    }
}