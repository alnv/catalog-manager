<?php

namespace CatalogManager;

class MasterInsertTag extends \Frontend {


    private $arrItemCache = [];


    public function getInsertTagValue( $strTag ) {

        global $objPage;

        $arrTags = explode( '::', $strTag );

        if ( is_array( $arrTags ) && $arrTags[0] == 'CTLG_MASTER' && isset( $arrTags[1] ) ) {

            if ( $this->arrItemCache[ $objPage->catalogMasterTable ] ) {

                return $this->arrItemCache[ $objPage->catalogMasterTable ][ $arrTags[1] ];
            }
            
            if ( $objPage->catalogUseMaster && \Input::get('auto_item') ) {

                $arrMaster = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `alias`=? OR `id`=?', $objPage->catalogMasterTable ) )->limit(1)->execute( \Input::get('auto_item'), (int)\Input::get('auto_item') )->row();
                $this->arrItemCache[ $objPage->catalogMasterTable ] = $arrMaster;

                if ( isset( $arrMaster[ $arrTags[1] ] ) ) {

                    return $arrMaster[ $arrTags[1] ];
                }

                return false;
            }
        }

        return false;
    }
}