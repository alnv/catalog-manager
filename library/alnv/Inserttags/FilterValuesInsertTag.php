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

                    break;
            }


            $intIndex = 0;
            $this->import( 'Input' );
            $strInputMethod = mb_strtolower( $strMethod );

            if ( !empty( $arrParameter ) && is_array( $arrParameter ) ) {

                foreach ( $arrParameter as $strParameter ) {

                    $varInputValue = $this->Input->{$strInputMethod}($strParameter);

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