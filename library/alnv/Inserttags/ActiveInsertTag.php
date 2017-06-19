<?php

namespace CatalogManager;

class ActiveInsertTag extends \Frontend {


    public function getInsertTagValue( $strTag ) {

        $arrTags = explode( '::', $strTag );

        if ( is_array( $arrTags ) && $arrTags[0] == 'CTLG_ACTIVE' && isset( $arrTags[1] ) ) {

            $varValue =  '';

            if ( \Input::get( $arrTags[1] ) ) $varValue = \Input::get( $arrTags[1] );
            if ( \Input::post( $arrTags[1] ) ) $varValue = \Input::post( $arrTags[1] ); # see #13
            if ( !$varValue ) $varValue = $arrTags[2] ? $arrTags[2] : '';
            if ( is_array( $varValue ) ) $varValue = implode( ',', $varValue );

            return $varValue;
        }

        return false;
    }
}