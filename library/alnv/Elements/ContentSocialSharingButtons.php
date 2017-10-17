<?php

namespace CatalogManager;

class ContentSocialSharingButtons extends \ContentElement {


    protected $strTemplate = 'ce_social_sharing_buttons';


    public function generate() {

        if ( !$this->catalogSocialSharingTable ) return null;

        return parent::generate();
    }


    protected function compile() {


        $this->import( 'CatalogMasterEntity' );
        $this->import( 'SocialSharingButtons' );

        $strTitleColumn = $this->catalogSocialSharingTitle;
        $strDescriptionColumn = $this->catalogSocialSharingDescription;
        $arrSocialButtons = Toolkit::deserialize( $this->catalogSocialSharingButtons );

        $this->CatalogMasterEntity->initialize( $this->catalogSocialSharingTable, [], false );
        $arrEntity = $this->CatalogMasterEntity->getMasterEntity();
        $arrEntity['masterUrl'] = \Idna::decode( \Environment::get('base') ) . \Environment::get('indexFreeRequest');

        $this->SocialSharingButtons->initialize( $arrSocialButtons, $this->catalogSocialSharingTemplate );
        $arrData = $this->SocialSharingButtons->getSocialSharingButtons( $arrEntity, $strTitleColumn, $strDescriptionColumn );

        $this->Template->setData( $arrData );
    }
}