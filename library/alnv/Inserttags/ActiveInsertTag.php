<?php

namespace CatalogManager;

class ActiveInsertTag extends \Frontend {


    public function getInsertTagValue( $strTag ) {

        $arrTags = explode( '::', $strTag );

        if ( is_array( $arrTags ) && $arrTags[0] == 'CTLG_ACTIVE' && isset( $arrTags[1] ) ) {

            $varValue = $arrTags[2] ? $arrTags[2] : '';
            $varValue =  \Input::get( $arrTags[1] ) ? \Input::get( $arrTags[1] ) : $varValue;

            if ( is_array( $varValue ) ) {

                $varValue = implode( ',', $varValue );
            }

            return $varValue;
        }

        return false;
    }
}