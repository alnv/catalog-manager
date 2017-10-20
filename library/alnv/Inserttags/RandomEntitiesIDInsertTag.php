<?php

namespace CatalogManager;

class RandomEntitiesIDInsertTag extends \Frontend {


    public function getInsertTagValue( $strTag ) {

        $arrTags = explode( '::', $strTag );

        if ( empty( $arrTags ) || !is_array( $arrTags ) ) {

            return false;
        }

        if ( isset( $arrTags[0] ) && $arrTags[0] == 'CTLG_RANDOM_ENTITY_IDS' ) {

            $strWhere = '';
            $strLimit = '';
            $strTablename = isset( $arrTags[1] ) ? $arrTags[1] : '';

            if ( !$this->Database->tableExists( $strTablename ) ) {

                return false;
            }

            if ( isset( $arrTags[2] ) && strpos( $arrTags[2], '?' ) !== false ) {

                $arrChunks = explode('?', urldecode( $arrTags[2] ), 2 );
                $strSource = \StringUtil::decodeEntities( $arrChunks[1] );
                $strSource = str_replace( '[&]', '&', $strSource );
                $arrParams = explode( '&', $strSource );

                foreach ( $arrParams as $strParam ) {

                    list( $strKey, $strOption ) = explode( '=', $strParam );

                    switch ( $strKey ) {

                        case 'query':

                            $strWhere = $strOption;

                            break;

                        case 'limit':

                            if ( is_numeric( $strOption ) ) {

                                $strLimit = 'LIMIT ' . $strOption;
                            }

                            break;
                    }
                }
            }

            if ( Toolkit::isEmpty( $strWhere ) && $this->Database->fieldExists( 'invisible', $strTablename ) ) {

                $strWhere = 'WHERE invisible != "1"';
            }

            $strIds = [];
            $objIds = $this->Database->prepare( sprintf( 'SELECT id FROM %s %s ORDER BY RAND() %s', $strTablename, $strWhere, $strLimit ) )->execute();

            if ( $objIds->numRows ) {

                while ( $objIds->next() ) {

                    $strIds[] = $objIds->id;
                }
            }

            return implode( ',', $strIds );
        }

        return false;
    }
}