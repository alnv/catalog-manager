<?php

namespace CatalogManager;

class MasterValueInsertTag extends \Frontend {


    private $arrItemCache = [];


    public function getInsertTagValue( $strTag ) {

        global $objPage;

        $arrTags = explode( '::', $strTag );

        if ( is_array( $arrTags ) && $arrTags[0] == 'CTLG_MASTER_VALUE' && isset( $arrTags[1] ) ) {

            $strCacheID = $objPage->catalogCatalogTable . '::' . $objPage->catalogCatalogColumn;

            if ( $this->arrItemCache[ $strCacheID ] ) {

                return $this->arrItemCache[ $strCacheID ][ $arrTags[1] ];
            }

            if ( $objPage->catalogUseMaster && \Input::get('auto_item') ) {

                $arrMaster = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `%s`=?', $objPage->catalogCatalogTable, $objPage->catalogCatalogColumn ) )->limit(1)->execute( \Input::get('auto_item') )->row();

                $this->arrItemCache[ $strCacheID ] = $arrMaster;

                if ( isset( $arrMaster[ $arrTags[1] ] ) ) {

                    return $arrMaster[ $arrTags[1] ];
                }

                return false;
            }
        }

        return false;
    }
}