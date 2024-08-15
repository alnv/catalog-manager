<?php

namespace Alnv\CatalogManagerBundle;

use Contao\DataContainer;
use Contao\Files;
use Contao\Image;
use Contao\System;

class IconGetter extends CatalogController
{

    protected string $strDirectory = 'files/catalog-manager';

    protected array $arrFileFormats = ['svg', 'png', 'jpg'];

    protected string $strCatalogDefaultIcon = 'catalog-icon.svg';


    public function setCatalogIcon($strTablename): string
    {

        $strIconname = $strTablename . '-' . 'icon';
        $strCustomIcon = $this->getIcon($strIconname);

        if ($strCustomIcon != '') return $strCustomIcon;

        return 'bundles/alnvcatalogmanager/icons/catalog-icon.svg';
    }


    public function setLanguageIcon($strLanguage): string
    {

        $strCustomIcon = $this->getIcon($strLanguage);

        if ($strCustomIcon != '') return $strCustomIcon;

        return 'bundles/alnvcatalogmanager/icons/catalog-icon.svg';
    }


    public function setTreeViewIcon($strTablename, $arrRow, $strLabel, DataContainer $dc = null, $strImageAttribute = '', $blnReturnImage = false, $blnProtected = false): string
    {

        $strIconname = $strTablename . '-' . 'tag';
        $strCustomIcon = $this->getIcon($strIconname);
        $strIcon = 'bundles/alnvcatalogmanager/icons/tag-icon.svg';

        if ($strCustomIcon != '') {
            $strIcon = $strCustomIcon;
        }

        $strImageAttribute = trim($strImageAttribute . ' data-icon="edit.gif" data-icon-disabled="header.svg" ');

        return Image::getHtml($strIcon, '', $strImageAttribute);
    }


    public function setToggleIcon($strTablename, $blnVisible): string
    {

        $strIconname = $strTablename . (!$blnVisible ? '_' : '');
        $strCustomIcon = $this->getIcon($strIconname);
        $strPath = 'bundles/alnvcatalogmanager/icons/';

        if ($blnVisible) {
            return $strCustomIcon ?: $strPath . 'featured.svg';
        }

        return $strCustomIcon ?: $strPath . 'featured_.svg';
    }


    public function createCatalogManagerDirectories(): void
    {

        $strRootDir = System::getContainer()->getParameter('kernel.project_dir');
        $objFile = Files::getInstance();

        if (!file_exists($strRootDir . '/' . $this->strDirectory)) {
            $objFile->mkdir($this->strDirectory);
        }
    }


    protected function iconExist($strIconname, $strFormat): bool
    {

        $strRootDir = System::getContainer()->getParameter('kernel.project_dir');

        if (file_exists($strRootDir . '/' . $this->strDirectory . '/' . $strIconname . '.' . $strFormat)) {
            return true;
        }

        return false;
    }


    protected function getIcon($strIconname): string
    {

        if ($this->iconExist($strIconname, 'svg')) {
            return $this->strDirectory . '/' . $strIconname . '.svg';
        }

        foreach ($this->arrFileFormats as $strFileFormat) {
            if ($this->iconExist($strIconname, $strFileFormat)) {
                return $this->strDirectory . '/' . $strIconname . '.' . $strFileFormat;
            }
        }

        return '';
    }
}