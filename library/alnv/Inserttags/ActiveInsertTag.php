<?php

namespace CatalogManager;

class ActiveInsertTag extends \Frontend {


    public function getInsertTagValue( $strTag ) {

        $arrTags = explode( '::', $strTag );

        if ( is_array( $arrTags ) && $arrTags[0] == 'CTLG_ACTIVE' && isset( $arrTags[1] ) ) {

            $strDefaultValue = $arrTags[2] ? $arrTags[2] : '';

            return \Input::get( $arrTags[1] ) ? \Input::get( $arrTags[1] ) : $strDefaultValue;
        }

        return false;
    }
}