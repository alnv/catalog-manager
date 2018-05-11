<?php

namespace CatalogManager;

class CatalogBreadcrumb extends \Frontend {


    public function initialize( $arrItems, $objModule ) {

        $blnShowInBreadcrumb = false;
        $strAlias = \Input::get('auto_item');
        $intLastIndex = count( $arrItems ) -1;
        $arrItem = $arrItems[ $intLastIndex ];

        if ( $arrItem['isActive'] && $arrItem['data']['catalogUseMaster'] && !Toolkit::isEmpty( $strAlias ) ) {

            $arrMasterItem = [];

            if ( isset( $arrItem['data']['catalogShowInBreadcrumb'] ) && $arrItem['data']['catalogShowInBreadcrumb'] ) {

                $blnShowInBreadcrumb = true;
                $arrItems[ $intLastIndex ]['isActive'] = false;
            }

            $strTable = $arrItem['data']['catalogMasterTable'];

            if ( $strTable && $this->Database->tableExists( $strTable ) ) {

                $objEntity = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `alias` = ? OR `id` = ?', $strTable ) )->limit(1)->execute( $strAlias, (int)$strAlias );

                if ( $objEntity->numRows ) {

                    $strHref = $arrItem['href'];

                    if ( !$arrItem['data']['catalogUseRouting'] ) {

                        $strHref = $this->generateHref( $arrItem['data']['id'], $objEntity->alias );
                    }
                    
                    if ( Toolkit::isEmpty( $strHref ) ) {

                        $strHref = $arrItem['href'];
                    }

                    $arrMasterItem['isActive'] = true;
                    $arrMasterItem['href'] = $strHref;
                    $arrMasterItem['data'] = $arrItem['data'];
                    $arrMasterItem['link'] = $objEntity->title;
                    $arrMasterItem['title'] = $objEntity->title;
                    $arrMasterItem['catalogAttributes'] = $objEntity->row();
                }

                if ( $blnShowInBreadcrumb ) {

                    $arrItems[] = $arrMasterItem;
                }

                else {

                    $arrItems[ $intLastIndex ] = $arrMasterItem;
                }
            }
        }

        return $arrItems;
    }


    protected function generateHref( $strPageID, $strAlias = '' ) {

        $objPage = \PageModel::findWithDetails( $strPageID );

        if ( $objPage !== null ) {

            return $this->generateUrl( $objPage->row(), $strAlias );
        }

        return '';
    }


    protected function generateUrl( $arrPage, $strAlias ) {

        if ( !is_array( $arrPage ) ) return '';

        return $this->generateFrontendUrl( $arrPage, ( $strAlias ? '/' . $strAlias : '' ) );
    }
}