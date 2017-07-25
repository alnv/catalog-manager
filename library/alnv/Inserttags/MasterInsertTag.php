<?php

namespace CatalogManager;

class MasterInsertTag extends \Frontend {


    protected $arrMaster = [];
    

    public function getInsertTagValue( $strTag ) {

        global $objPage;

        $arrTags = explode( '::', $strTag );

        if ( empty( $arrTags ) || !is_array( $arrTags ) ) {

            return false;
        }

        if ( isset( $arrTags[0] ) && $arrTags[0] == 'CTLG_MASTER' && $objPage->catalogUseMaster ) {

            $strFieldname = $arrTags[1];
            $strDefaultValue = $arrTags[2] ? $arrTags[2] : '';

            if ( !$strFieldname || !$objPage->catalogMasterTable ) return false;

            $this->getMasterEntity( $objPage->catalogMasterTable );

            if ( Toolkit::isEmpty( $this->arrMaster[ $objPage->catalogMasterTable ][ $strFieldname ] ) && !Toolkit::isEmpty( $strDefaultValue ) ) {

                return $strDefaultValue;
            }

            return $this->arrMaster[ $objPage->catalogMasterTable ][ $strFieldname ] ? $this->arrMaster[ $objPage->catalogMasterTable ][ $strFieldname ] : '';
        }

        return false;
    }


    protected function getMasterEntity( $strTable ) {

        $strAlias = \Input::get('auto_item');

        if ( !empty( $this->arrMaster[ $strTable ] ) && is_array( $this->arrMaster[ $strTable ] ) ) {

            return null;
        }

        if ( Toolkit::isEmpty( $strAlias ) ) $this->arrMaster[ $strTable ] = [];

        $objMaster = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `alias`=? OR `id`=?', $strTable ) )->limit(1)->execute( $strAlias, (int)$strAlias );

        if ( $objMaster->numRows ) $this->arrMaster[ $strTable ] = $objMaster->row();

        if ( !isset( $this->arrMaster[ $strTable ] ) ) $this->arrMaster[ $strTable ] = [];
    }
}