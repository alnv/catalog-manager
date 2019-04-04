<?php

namespace CatalogManager;

class MasterInsertTag extends \Frontend {


    public function getInsertTagValue( $strTag ) {

        global $objPage;

        $arrTags = explode( '::', $strTag );

        if ( empty( $arrTags ) || !is_array( $arrTags ) ) {

            return false;
        }

        if ( isset( $arrTags[0] ) && $arrTags[0] == 'CTLG_MASTER' && $objPage->catalogUseMaster ) {

            $this->import( 'CatalogMasterEntity' );

            $strText = '';
            $arrJoinOnly = [];
            $blnAddText = false;
            $strDefaultValue = '';
            $blnJoinParent = false;
            $blnJoinFields = false;
            $blnParseValues = false;
            $strFieldname = $arrTags[1] ?: '';
            $strTable = $objPage->catalogMasterTable;

            if ( !$strFieldname || !$strTable ) return false;

            if ( isset( $arrTags[2] ) && strpos( $arrTags[2], '?' ) !== false ) {

                $arrChunks = explode('?', urldecode( $arrTags[2] ), 2 );
                $strSource = \StringUtil::decodeEntities( $arrChunks[1] );
                $strSource = str_replace( '[&]', '&', $strSource );
                $arrParams = explode( '&', $strSource );

                foreach ( $arrParams as $strParam ) {

                    list( $strKey, $strOption ) = explode( '=', $strParam );

                    switch ( $strKey ) {

                        case 'default':

                            $strDefaultValue = $strOption ?: '';

                            break;

                        case 'parse':

                            $blnParseValues = $strOption ? true : false;

                            break;

                        case 'joinParent':

                            $blnJoinParent = $strOption ? true : false;

                            break;

                        case 'joinFields':

                            $blnJoinFields = $strOption ? true : false;

                            break;

                        case 'addText':

                            $blnAddText = true;
                            $strText = $strOption ? $strOption : '';

                            break;

                        case 'joinOnly':

                            $arrFields = explode( ',', $strOption );

                            if ( !empty( $arrFields ) && is_array( $arrFields ) ) {

                                $arrJoinOnly = $arrFields;
                            }

                            break;
                    }
                }
            }

            else {

                $strDefaultValue = Toolkit::isEmpty( $arrTags[2] ) ? '' : $arrTags[2];
            }

            $this->CatalogMasterEntity->initialize( $strTable, [

                'joinOnly' => $arrJoinOnly,
                'joinFields' => $blnJoinFields,
                'joinParent' => $blnJoinParent
            ]);

            $arrMaster = $this->CatalogMasterEntity->getMasterEntity( $blnParseValues );

            if ( Toolkit::isEmpty( $arrMaster[ $strFieldname ] ) && !Toolkit::isEmpty( $strDefaultValue ) ) {

                return $strDefaultValue;
            }

            $varValue = Toolkit::isEmpty( $arrMaster[ $strFieldname ] ) ? '' : $arrMaster[ $strFieldname ];
            $varValue = $this->setValue( $varValue, $arrTags[3] );

            if ( $blnAddText && $varValue ) $varValue .= $strText;

            return $varValue;
        }

        return false;
    }


    protected function setValue( $varValue, $strKey = '' ) {

        if ( !is_array( $varValue ) ) return $varValue;

        if ( Toolkit::isAssoc( $varValue ) ) {

            $strKeyname = $strKey ?: '';

            if ( $strKeyname && isset( $varValue[ $strKeyname ] ) ) $varValue = $varValue[ $strKeyname ];
        }

        if ( is_array( $varValue ) ) return implode( ', ' , $varValue );

        return $varValue;
    }
}