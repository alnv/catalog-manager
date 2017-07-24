<?php

namespace CatalogManager;

class MasterInsertTag extends \Frontend {


    private $arrItemCache = [];


    public function getInsertTagValue( $strTag ) {

        global $objPage;

        $arrTags = explode( '::', $strTag );

        if ( is_array( $arrTags ) && $arrTags[0] == 'CTLG_MASTER' && isset( $arrTags[1] ) && $objPage->catalogUseMaster ) {

            if ( $this->arrItemCache[ $objPage->catalogMasterTable ] ) {

                $strValue = $this->arrItemCache[ $objPage->catalogMasterTable ][ $arrTags[1] ];

                if ( Toolkit::isEmpty( $strValue ) && isset( $arrTags[2] ) ) {

                    $strValue = $arrTags[2];
                }

                return $strValue;
            }

            $arrMaster = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `alias`=? OR `id`=?', $objPage->catalogMasterTable ) )->limit(1)->execute( \Input::get('auto_item'), (int)\Input::get('auto_item') )->row();
            $this->arrItemCache[ $objPage->catalogMasterTable ] = $arrMaster;

            if ( !Toolkit::isEmpty( $arrMaster[ $arrTags[1] ] ) ) {

                return $arrMaster[ $arrTags[1] ];
            }

            elseif ( isset( $arrTags[2] ) ) {

                return $arrTags[2];
            }

            return false;
        }

        return false;
    }
}