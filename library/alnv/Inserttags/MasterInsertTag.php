<?php

namespace CatalogManager;

class MasterInsertTag extends \Frontend {


    protected $arrCatalogFields = [];
    protected $blnJoinParent = false;
    protected $blnJoinFields = false;
    protected $arrJoinFields = [];
    protected $arrJoinOnly = [];
    protected $arrCatalog = [];
    protected $arrMaster = [];
    protected $strHash = '';
    protected $strTable;
    

    public function getInsertTagValue( $strTag ) {

        global $objPage;

        $arrTags = explode( '::', $strTag );

        if ( empty( $arrTags ) || !is_array( $arrTags ) ) {

            return false;
        }

        if ( isset( $arrTags[0] ) && $arrTags[0] == 'CTLG_MASTER' && $objPage->catalogUseMaster ) {

            $this->import( 'CatalogMasterEntity' );

            $strDefaultValue = '';
            $blnParseValues = false;
            $blnPreventCache = false;
            $strFieldname = $arrTags[1];
            $this->strTable = $objPage->catalogMasterTable;

            if ( !$strFieldname || !$this->strTable ) return false;

            if ( isset( $arrTags[2] ) && strpos( $arrTags[2], '?' ) !== false ) {

                $arrChunks = explode('?', urldecode( $arrTags[2] ), 2 );
                $strSource = \StringUtil::decodeEntities( $arrChunks[1] );
                $strSource = str_replace( '[&]', '&', $strSource );
                $arrParams = explode( '&', $strSource );

                foreach ( $arrParams as $strParam ) {

                    list( $strKey, $strOption ) = explode( '=', $strParam );

                    switch ( $strKey ) {

                        case 'default':

                            $strDefaultValue = $strOption;

                            break;

                        case 'parse':

                            $blnPreventCache = $this->strHash != md5( $strSource );
                            $blnParseValues = $strOption ? true : false;

                            break;

                        case 'joinParent':

                            $blnPreventCache = $this->strHash != md5( $strSource );
                            $this->blnJoinParent = $strOption ? true : false;

                            break;

                        case 'joinFields':

                            $blnPreventCache = $this->strHash != md5( $strSource );
                            $this->blnJoinFields = $strOption ? true : false;

                            break;

                        case 'joinOnly':

                            $blnPreventCache = $this->strHash != md5( $strSource );
                            $arrFields = explode( ',', $strOption );

                            if ( !empty( $arrFields ) && is_array( $arrFields ) ) {

                                $this->arrJoinOnly = $arrFields;
                            }

                            break;
                    }
                }

                $this->strHash = md5( $strSource );
            }

            else {

                $strDefaultValue = Toolkit::isEmpty( $arrTags[2] ) ? '' : $arrTags[2];
            }

            if ( empty( $this->arrCatalog ) ) {

                $this->CatalogMasterEntity->initialize( $this->strTable, $this->arrJoinOnly );

                $this->arrCatalog = $this->CatalogMasterEntity->getCatalog();
                $this->arrJoinFields = $this->CatalogMasterEntity->getJoinFields();
                $this->arrCatalogFields = $this->CatalogMasterEntity->getCatalogFields();

            }

            if ( empty( $this->arrMaster ) || $blnPreventCache ) {

                $this->arrMaster = $this->CatalogMasterEntity->getMasterEntity( $blnParseValues );
            }

            if ( Toolkit::isEmpty( $this->arrMaster[ $strFieldname ] ) && !Toolkit::isEmpty( $strDefaultValue ) ) {

                return $strDefaultValue;
            }

            $varValue = Toolkit::isEmpty( $this->arrMaster[ $strFieldname ] ) ? '' : $this->arrMaster[ $strFieldname ];

            if ( is_array( $varValue ) ) {

                $strKeyname = $arrTags[3] ?: '';

                if ( $strKeyname && isset( $varValue[ $strKeyname ] ) ) {

                    $varValue = $varValue[ $strKeyname ];
                }
            }

            return $varValue;
        }

        return false;
    }
}