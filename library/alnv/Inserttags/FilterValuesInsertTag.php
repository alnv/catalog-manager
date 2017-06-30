<?php

namespace CatalogManager;

class FilterValuesInsertTag extends \Frontend {


    public function getInsertTagValue( $strTag ) {

        $arrTags = explode( '::', $strTag );

        if ( is_array( $arrTags ) && $arrTags[0] == 'CTLG_FILTER_VALUES' ) {

            $varValue =  '';
            $arrParameter = [];
            $arrMethods = [ 'POST', 'GET' ];
            $strMethod = $arrTags[1] ? $arrTags[1] : 'GET';

            if ( !in_array( $strMethod, $arrMethods ) )  $strMethod = 'GET';

            switch ( $strMethod ) {

                case 'GET':

                    $arrParameter = array_keys( $_GET );

                    break;

                case 'POST':

                    $arrParameter = array_keys( $_POST );

                    if ( empty( $arrParameter ) ) {

                        $arrParameter = array_keys( $_COOKIE );
                    }

                    break;
            }


            $intIndex = 0;
            $this->import( 'CatalogInput' );
            
            if ( !empty( $arrParameter ) && is_array( $arrParameter ) ) {

                foreach ( $arrParameter as $strParameter ) {

                    $varInputValue = $this->CatalogInput->getActiveValue( $strParameter );

                    if ( is_null( $varInputValue ) || $varInputValue === '' ) continue;

                    if ( is_array( $varInputValue ) ) {

                        foreach ( $varInputValue as $strValue ) {

                            $varValue .= $this->parseToUri( $strParameter, $strValue, true, $intIndex );
                        }
                    }

                    if ( is_string( $varInputValue ) ) {

                        $varValue .= $this->parseToUri( $strParameter, $varInputValue, false, $intIndex );
                    }

                    $intIndex++;
                }
            }

            return $varValue;
        }

        return false;
    }

    protected function parseToUri( $strParameter, $strValue, $blnMultiple, $intIndex ) {

        if ( !$strValue ) return '';

        return ( $intIndex ? '&' : '?' ) . $strParameter . ( $blnMultiple ? '[]' : '' ) . '=' . $strValue;
    }
}