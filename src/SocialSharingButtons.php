<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Idna;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class SocialSharingButtons extends CatalogController
{

    protected array $arrCssID = [];

    protected array $arrHeadline = [];

    protected array $arrSocialSharingButtons = [];

    protected string $strTemplate = 'ce_social_sharing_buttons';


    public function initialize($arrSocialSharingButtons, $strTemplate = '', $blnDefaultTheme = true, $arrTemplateData = [])
    {

        $blnIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));
        $this->strTemplate = $strTemplate ? $strTemplate : $this->strTemplate;
        $this->arrSocialSharingButtons = is_array($arrSocialSharingButtons) ? $arrSocialSharingButtons : [];
        $this->arrCssID = Toolkit::deserialize($arrTemplateData['catalogSocialSharingCssID']);
        $this->arrHeadline = Toolkit::deserialize($arrTemplateData['catalogSocialSharingHeadline']);

        if (!$blnIsBackend && $blnDefaultTheme) {
            $GLOBALS['TL_CSS']['catalogManagerSocialSharingButtons'] = 'bundles/alnvcatalogmanager/social-sharing-buttons.css';
        }
    }

    public function render($arrData, $strTitleColumn, $strDescriptionColumn)
    {

        $objMainTemplate = new FrontendTemplate($this->strTemplate);
        $arrData = $this->getSocialSharingButtons($arrData, $strTitleColumn, $strDescriptionColumn);

        $arrData['customID'] = Toolkit::isEmpty($this->arrCssID[0]) ? '' : $this->arrCssID[0];
        $arrData['customClass'] = Toolkit::isEmpty($this->arrCssID[1]) ? '' : ' ' . $this->arrCssID[1];
        $arrData['hl'] = Toolkit::isEmpty($this->arrHeadline['unit']) ? '' : $this->arrHeadline['unit'];
        $arrData['headline'] = Toolkit::isEmpty($this->arrHeadline['value']) ? '' : $this->arrHeadline['value'];

        $objMainTemplate->setData($arrData);

        return $objMainTemplate->parse();
    }

    public function getSocialSharingButtons($arrData, $strTitleColumn, $strDescriptionColumn): array
    {

        global $objPage;

        $strOutput = '';
        $strShareUrl = Idna::decode(Environment::get('base')) . $arrData['masterUrl'];

        $arrReturn = [
            'data' => $arrData,
            'shareUrl' => $strShareUrl,
            'title' => $arrData[$strTitleColumn] ? strip_tags($arrData[$strTitleColumn]) : $objPage->pageTitle,
            'description' => $arrData[$strDescriptionColumn] ? strip_tags($arrData[$strDescriptionColumn]) : $objPage->description,
            'mail' => $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['mail'],
            'share' => $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['share'],
            'tweet' => $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['tweet'],
        ];

        foreach ($this->arrSocialSharingButtons as $strButton) {
            $strTemplate = 'ctlg_social_button_' . $strButton;
            $objTemplate = new FrontendTemplate($strTemplate);
            $objTemplate->setData($arrReturn);
            $strOutput .= $objTemplate->parse();
        }

        $arrReturn['output'] = $strOutput;

        return $arrReturn;
    }
}
