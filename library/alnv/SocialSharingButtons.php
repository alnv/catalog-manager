<?php

namespace CatalogManager;

class SocialSharingButtons extends CatalogController {


    protected $arrSocialSharingButtons = [];
    protected $strTemplate = 'ctlg_social_sharing_buttons';


    public function initialize( $arrSocialSharingButtons, $strTemplate = '' ) {

        $this->strTemplate = $strTemplate ? $strTemplate : $this->strTemplate;
        $this->arrSocialSharingButtons = is_array( $arrSocialSharingButtons ) ? $arrSocialSharingButtons : [];
    }


    public function render( $arrData, $strTitleColumn, $strDescriptionColumn ) {

        global $objPage;
        $strSocialButtons = '';
        $objMainTemplate = new \FrontendTemplate( $this->strTemplate );
        $strShareUrl = \Idna::decode( \Environment::get('base') ) . $arrData['masterUrl'];
        
        $arrSocialShareData = [

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
            $objTemplate->setData( $arrSocialShareData );
            $strSocialButtons .= $objTemplate->parse();
        }

        $arrSocialShareData['output'] = $strSocialButtons;
        $objMainTemplate->setData( $arrSocialShareData );

        return $objMainTemplate->parse();
    }
}
