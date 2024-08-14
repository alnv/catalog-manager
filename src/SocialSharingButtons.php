<?php

namespace Alnv\CatalogManagerBundle;

class SocialSharingButtons extends CatalogController {

    protected $arrCssID = [];
    protected $arrHeadline = [];
    protected $arrSocialSharingButtons = [];
    protected $strTemplate = 'ce_social_sharing_buttons';


    public function initialize( $arrSocialSharingButtons, $strTemplate = '', $blnDefaultTheme = true, $arrTemplateData = [] ) {

        $this->strTemplate = $strTemplate ? $strTemplate : $this->strTemplate;
        $this->arrSocialSharingButtons = is_array( $arrSocialSharingButtons ) ? $arrSocialSharingButtons : [];

        $this->arrCssID = Toolkit::deserialize( $arrTemplateData['catalogSocialSharingCssID'] );
        $this->arrHeadline = Toolkit::deserialize( $arrTemplateData['catalogSocialSharingHeadline'] );

        if ( TL_MODE == 'FE' && $blnDefaultTheme ) {

            $GLOBALS['TL_CSS']['catalogManagerSocialSharingButtons'] = $GLOBALS['TL_CONFIG']['debugMode']
                ? 'system/modules/catalog-manager/assets/social-sharing-buttons.css'
                : 'system/modules/catalog-manager/assets/social-sharing-buttons.css';
        }
    }


    public function render( $arrData, $strTitleColumn, $strDescriptionColumn ) {

        $objMainTemplate = new \FrontendTemplate( $this->strTemplate );
        $arrData = $this->getSocialSharingButtons( $arrData, $strTitleColumn, $strDescriptionColumn );

        $arrData['customID'] = Toolkit::isEmpty( $this->arrCssID[0] ) ? '' : $this->arrCssID[0];
        $arrData['customClass'] = Toolkit::isEmpty( $this->arrCssID[1] ) ? '' : ' ' . $this->arrCssID[1];
        $arrData['hl'] = Toolkit::isEmpty( $this->arrHeadline['unit'] ) ? '' : $this->arrHeadline['unit'];
        $arrData['headline'] = Toolkit::isEmpty( $this->arrHeadline['value'] ) ? '' : $this->arrHeadline['value'];

        $objMainTemplate->setData( $arrData );

        return $objMainTemplate->parse();
    }


    public function getSocialSharingButtons( $arrData, $strTitleColumn, $strDescriptionColumn ) {

        global $objPage;

        $strOutput= '';
        $strShareUrl = \Idna::decode( \Environment::get('base') ) . $arrData['masterUrl'];

        $arrReturn = [

            'data' => $arrData,
            'shareUrl' => $strShareUrl,
            'title' => $arrData[ $strTitleColumn ] ? strip_tags( $arrData[ $strTitleColumn ] ) : $objPage->pageTitle,
            'description' => $arrData[ $strDescriptionColumn ] ? strip_tags( $arrData[ $strDescriptionColumn ] ) : $objPage->description,

            'mail' => $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['mail'],
            'share' => $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['share'],
            'tweet' => $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['tweet'],
        ];

        foreach ( $this->arrSocialSharingButtons as $strButton ) {

            $strTemplate = 'ctlg_social_button_' . $strButton;
            $objTemplate = new \FrontendTemplate( $strTemplate );
            $objTemplate->setData( $arrReturn );

            $strOutput .= $objTemplate->parse();
        }

        $arrReturn['output'] = $strOutput;

        return $arrReturn;
    }
}
