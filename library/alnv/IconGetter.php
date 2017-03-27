<?php

namespace CatalogManager;

class IconGetter extends CatalogController {


    protected $strDirectory = 'files/catalog-manager';
    protected $arrFileFormats = [ 'svg', 'png', 'jpg' ];
    protected $strCatalogDefaultIcon = 'catalog-icon.gif';


    public function setCatalogIcon( $strTablename ) {

        $strIconname = $strTablename . '-' . 'icon';
        $strCustomIcon = $this->getIcon( $strIconname );

        if ( $strCustomIcon != '' ) return $strCustomIcon;

        return 'system/modules/catalog-manager/assets/icons/catalog-icon.gif';
    }


    public function setTreeViewIcon( $strTablename, $arrRow, $strLabel, \DataContainer $dc = null, $strImageAttribute = '', $blnReturnImage = false, $blnProtected = false ) {

        $strIconname = $strTablename . '-' . 'tag';
        $strCustomIcon = $this->getIcon( $strIconname );
        $strIcon = 'system/modules/catalog-manager/assets/icons/tag-icon.gif';

        if ( $strCustomIcon != '' ) {

            $strIcon = $strCustomIcon;
        }

        $strImageAttribute = trim( $strImageAttribute . ' data-icon="edit.gif" data-icon-disabled="header.gif" ');

        if ( $arrRow['pid'] == '0' ) {

            $strLabel = '<strong>' . $strLabel . '</strong>';
        }

        return \Image::getHtml( $strIcon, '', $strImageAttribute ) . ' <span>' . $strLabel . '</span>';
    }


    public function setToggleIcon( $strTablename, $blnVisible ) {

        $strIconname = $strTablename . ( !$blnVisible ? '_' : '' );
        $strCustomIcon = $this->getIcon( $strIconname );
        $strPath = 'system/modules/catalog-manager/assets/icons/';

        if ( $blnVisible ) {

            return $strCustomIcon ? $strCustomIcon : $strPath . 'featured.gif';
        }

        return $strCustomIcon ? $strCustomIcon : $strPath . 'featured_.gif';
    }


    public function createCatalogManagerDirectories() {

        $objFile = \Files::getInstance();

        if ( !file_exists( TL_ROOT . '/' . $this->strDirectory ) ) {

            $objFile->mkdir( $this->strDirectory );
        }
    }


    protected function iconExist( $strIconname, $strFormat ) {

        if ( file_exists( TL_ROOT . '/' . $this->strDirectory . '/' . $strIconname . '.' . $strFormat ) ) {

            return true;
        }

        return false;
    }


    protected function getIcon( $strIconname ) {

        if ( $this->iconExist( $strIconname, 'gif' ) ) {

            return $this->strDirectory . '/' . $strIconname . '.gif';
        }

        foreach ( $this->arrFileFormats as $strFileFormat ) {

            if ( $this->iconExist( $strIconname, $strFileFormat ) ) {

                return $this->strDirectory . '/' . $strIconname . '.' . $strFileFormat;
            }
        }

        return '';
    }
}