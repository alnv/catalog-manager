<?php

namespace CatalogManager;

class ActiveInsertTag extends \Frontend {


    public function getInsertTagValue( $strTag ) {

        $arrTags = explode( '::', $strTag );

        if ( is_array( $arrTags ) && $arrTags[0] == 'CTLG_ACTIVE' && isset( $arrTags[1] ) ) {

            global $objPage;

            $varValue =  '';

            if ( \Input::get( $arrTags[1] ) ) $varValue = \Input::get( $arrTags[1] );
            if ( \Input::post( $arrTags[1] ) ) $varValue = \Input::post( $arrTags[1] );

            if ( isset( $arrTags[2] ) && strpos( $arrTags[2], '?' ) !== false ) {

                $arrChunks = explode('?', urldecode( $arrTags[2] ), 2 );
                $strSource = \StringUtil::decodeEntities( $arrChunks[1] );
                $strSource = str_replace( '[&]', '&', $strSource );
                $arrParams = explode( '&', $strSource );

                foreach ( $arrParams as $strParam ) {

                    list( $strKey, $strOption ) = explode( '=', $strParam );

                    switch ( $strKey ) {

                        case 'default':

                            $varValue = $strOption;

                            break;

                        case 'isDate':

                            $strOption = $strOption ? $strOption : $objPage->dateFormat;

                            if ( is_array( $varValue ) ) {

                                foreach ( $varValue as $strK => $strV ) {

                                    if ( !$strV ) continue;

                                    $objDate = new \Date( $strV, $strOption );
                                    $varValue[ $strK ] = $objDate->tstamp;
                                }
                            }

                            elseif( $varValue ) {

                                $objDate = new \Date( $varValue, $strOption );
                                $varValue = $objDate->tstamp;
                            }

                            break;
                    }
                }
            }

            elseif( !$varValue ) {

                $varValue = $arrTags[2] ? $arrTags[2] : '';
            }

            if ( is_array( $varValue ) ) $varValue = implode( ',', $varValue );

            return $varValue;
        }

        return false;
    }
}