<?php

namespace Alnv\CatalogManagerBundle\Elements;

use Alnv\CatalogManagerBundle\CatalogMasterEntity;
use Alnv\CatalogManagerBundle\SocialSharingButtons;
use Alnv\CatalogManagerBundle\Toolkit;
use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\Environment;
use Contao\Idna;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class ContentSocialSharingButtons extends ContentElement
{

    protected $strTemplate = 'ce_social_sharing_buttons';

    public function generate()
    {

        if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . \strtoupper($GLOBALS['TL_LANG']['CTE']['catalogSocialSharingButtons'][0]) . ' ###';
            return $objTemplate->parse();
        }

        if (!$this->catalogSocialSharingTable) return null;

        return parent::generate();
    }

    protected function compile()
    {

        $this->import(CatalogMasterEntity::class, 'CatalogMasterEntity');
        $this->import(SocialSharingButtons::class, 'SocialSharingButtons');

        $this->cssID = Toolkit::deserialize($this->cssID);

        $strTitleColumn = $this->catalogSocialSharingTitle;
        $strDescriptionColumn = $this->catalogSocialSharingDescription;
        $blnDefaultTheme = $this->catalogDisableSocialSharingCSS ? false : true;
        $arrSocialButtons = Toolkit::deserialize($this->catalogSocialSharingButtons);

        $this->CatalogMasterEntity->initialize($this->catalogSocialSharingTable, [], false);
        $arrEntity = $this->CatalogMasterEntity->getMasterEntity();
        $arrEntity['masterUrl'] = Idna::decode(Environment::get('base')) . Environment::get('indexFreeRequest');
        $this->SocialSharingButtons->initialize($arrSocialButtons, $this->catalogSocialSharingTemplate, $blnDefaultTheme);
        $arrData = $this->SocialSharingButtons->getSocialSharingButtons($arrEntity, $strTitleColumn, $strDescriptionColumn);

        $arrData['customClass'] = Toolkit::isEmpty($this->cssID[1]) ? '' : ' ' . $this->cssID[1];
        $arrData['customID'] = Toolkit::isEmpty($this->cssID[0]) ? '' : $this->cssID[0];
        $arrData['headline'] = Toolkit::isEmpty($this->headline) ? '' : $this->headline;
        $arrData['hl'] = Toolkit::isEmpty($this->hl) ? '' : $this->hl;

        $this->Template->setData($arrData);
    }
}