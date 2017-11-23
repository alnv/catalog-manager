<?php

namespace CatalogManager;

class TimestampInsertTag extends \Frontend {


    public function getInsertTagValue( $strTag ) {

        $arrTags = explode( '::', $strTag );

        if ( empty( $arrTags ) || !is_array( $arrTags ) ) {

            return false;
        }

        if ( isset( $arrTags[0] ) && $arrTags[0] == 'CTLG_TIMESTAMP' ) {

            $objDate = new \Date();
            $intTimestamp = $objDate->tstamp;

            if ( isset( $arrTags[1] ) && strpos( $arrTags[1], '?' ) !== false ) {

                $arrChunks = explode('?', urldecode( $arrTags[1] ), 2 );
                $strSource = \StringUtil::decodeEntities( $arrChunks[1] );
                $strSource = str_replace( '[&]', '&', $strSource );
                $arrParams = explode( '&', $strSource );

                foreach ( $arrParams as $strParam ) {

                    list( $strKey, $strOption ) = explode( '=', $strParam );

                    $strOption = $strOption ? (int) $strOption : 0;

                    switch ( $strKey ) {

                        case 'add':

                            $intTimestamp = $intTimestamp + $strOption;

                            break;

                        case 'subtract':

                            $intTimestamp = $intTimestamp - $strOption;

                            break;

                        case 'multiply':

                            $intTimestamp = $intTimestamp * $strOption;

                            break;

                        case 'divide':

                            if ( $strOption > 0 ) {

                                $intTimestamp = $intTimestamp / $strOption;
                            }

                            break;
                    }
                }
            }
            
            return $intTimestamp;
        }

        return false;
    }
}