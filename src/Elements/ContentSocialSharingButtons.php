<?php

namespace Alnv\CatalogManagerBundle\Elements;

class ContentSocialSharingButtons extends \ContentElement {


    protected $strTemplate = 'ce_social_sharing_buttons';


    public function generate() {

        if ( TL_MODE == 'BE' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . utf8_strtoupper( $GLOBALS['TL_LANG']['CTE']['catalogSocialSharingButtons'][0] ) . ' ###';

            return $objTemplate->parse();
        }

        if ( !$this->catalogSocialSharingTable ) return null;

        return parent::generate();
    }


    protected function compile() {


        $this->import( 'CatalogMasterEntity' );
        $this->import( 'SocialSharingButtons' );

        $this->cssID = Toolkit::deserialize( $this->cssID );

        $strTitleColumn = $this->catalogSocialSharingTitle;
        $strDescriptionColumn = $this->catalogSocialSharingDescription;
        $blnDefaultTheme = $this->catalogDisableSocialSharingCSS ? false : true;
        $arrSocialButtons = Toolkit::deserialize( $this->catalogSocialSharingButtons );

        $this->CatalogMasterEntity->initialize( $this->catalogSocialSharingTable, [], false );
        $arrEntity = $this->CatalogMasterEntity->getMasterEntity();
        $arrEntity['masterUrl'] = \Idna::decode( \Environment::get('base') ) . \Environment::get('indexFreeRequest');
        $this->SocialSharingButtons->initialize( $arrSocialButtons, $this->catalogSocialSharingTemplate, $blnDefaultTheme );
        $arrData = $this->SocialSharingButtons->getSocialSharingButtons( $arrEntity, $strTitleColumn, $strDescriptionColumn );

        $arrData['customClass'] = Toolkit::isEmpty( $this->cssID[1] ) ? '' : ' ' . $this->cssID[1];
        $arrData['customID'] = Toolkit::isEmpty( $this->cssID[0] ) ? '' : $this->cssID[0];
        $arrData['headline'] = Toolkit::isEmpty( $this->headline ) ? '' : $this->headline;
        $arrData['hl'] = Toolkit::isEmpty( $this->hl ) ? '' : $this->hl;

        $this->Template->setData( $arrData );
    }
}