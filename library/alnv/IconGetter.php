<?php

namespace CatalogManager;

class IconGetter extends CatalogController {


    protected $strDirectory = 'files/catalog-manager';
    protected $arrFileFormats = [ 'svg', 'png', 'jpg' ];
    protected $strCatalogDefaultIcon = 'catalog-icon.gif';


    public function setCatalogIcon( $strTablename ) {

        $strIconname = $strTablename . '-' . 'icon';

        if ( $this->iconExist( $strIconname, 'gif' ) ) {

            return $this->strDirectory . '/' . $strIconname . '.gif';
        }

        foreach ( $this->arrFileFormats as $strFileFormat ) {

            if ( $this->iconExist( $strIconname, $strFileFormat ) ) {

                return $this->strDirectory . '/' . $strIconname . '.' . $strFileFormat;
            }
        }

        return 'system/modules/catalog-manager/assets/icons/catalog-icon.gif';
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
}