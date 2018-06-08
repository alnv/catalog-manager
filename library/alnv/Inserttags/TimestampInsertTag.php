<?php

namespace CatalogManager;

class TimestampInsertTag extends \Frontend {


    public function getInsertTagValue( $strTag ) {

        $arrTags = explode( '::', $strTag );

        if ( empty( $arrTags ) || !is_array( $arrTags ) ) {

            return false;
        }

        if ( isset( $arrTags[0] ) && $arrTags[0] == 'CTLG_TIMESTAMP' ) {

            $objToday = new \Date();
            $objDate = new \Date( $objToday->date );
            $intTstamp = $objDate->dayBegin;

            if ( isset( $arrTags[1] ) && strpos( $arrTags[1], '?' ) !== false ) {

                $arrChunks = explode('?', urldecode( $arrTags[1] ), 2 );
                $strSource = \StringUtil::decodeEntities( $arrChunks[1] );
                $strSource = str_replace( '[&]', '&', $strSource );
                $arrParams = explode( '&', $strSource );

                foreach ( $arrParams as $strParam ) {

                    list( $strKey, $strOption ) = explode( '=', $strParam );

                    switch ( $strKey ) {

                        case 'watch':

                            $objInput = new CatalogInput();
                            $intWatchValue = $objInput->getValue( $strOption );

                            if ( $intWatchValue ) $intTstamp = (int) $intWatchValue;

                            break;

                        case 'add':

                            $strOption = $strOption ? (int) $strOption : 0;
                            $intTstamp = $intTstamp + $strOption;

                            break;

                        case 'subtract':

                            $strOption = $strOption ? (int) $strOption : 0;
                            $intTstamp = $intTstamp - $strOption;

                            break;

                        case 'multiply':

                            $strOption = $strOption ? (int) $strOption : 0;
                            $intTstamp = $intTstamp * $strOption;

                            break;

                        case 'divide':

                            $strOption = $strOption ? (int) $strOption : 0;

                            if ( $strOption > 0 ) {

                                $intTstamp = $intTstamp / $strOption;
                            }

                            break;
                    }
                }
            }
            
            return $intTstamp;
        }

        return false;
    }
}