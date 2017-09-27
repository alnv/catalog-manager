<?php

namespace CatalogManager;

class SearchIndexBuilder extends \Frontend {


    public function initialize( $arrPages, $intRoot = 0, $blnIsSitemap = false ) {

        $arrRoot = [];
        $this->import( 'SQLQueryBuilder' );

        if ( $intRoot > 0 ) $arrRoot = $this->Database->getChildRecords( $intRoot, 'tl_page' );

        $arrProcessed = [];
        $objModules = $this->Database->prepare( 'SELECT * FROM tl_module WHERE type = ? OR type = ?' )->execute( 'catalogUniversalView', 'catalogMasterView' );

        while ( $objModules->next() ) {

            if ( !$objModules->catalogUseMasterPage ) continue;

            if ( !$objModules->catalogMasterPage ) continue;

            if ( !$objModules->catalogTablename ) continue;

            if ( !empty( $arrRoot ) && !in_array( $objModules->catalogMasterPage, $arrRoot ) ) continue;

            if ( !isset( $arrProcessed[ $objModules->catalogMasterPage ] ) ) {

                $objParent = $this->getPageModelWithDetailsByID( $objModules->catalogMasterPage );

                if ( !$objParent ) continue;

                $strDomain = ( $objParent->rootUseSSL ? 'https://' : 'http://' ) . ( $objParent->domain ?: \Environment::get( 'host' ) ) . TL_PATH . '/';
                $arrProcessed[ $objModules->catalogMasterPage ] = $strDomain . $this->generateFrontendUrl( $objParent->row(), ( ( \Config::get( 'useAutoItem' ) && !\Config::get( 'disableAlias' ) ) ? '/%s' : '/items/%s' ), $objParent->language );
            }

            $arrQuery = [

                'where' => [],
                'table' => $objModules->catalogTablename
            ];

            $strUrl = $arrProcessed[ $objModules->catalogMasterPage ];
            $strQuery = sprintf( 'SELECT * FROM %s', $objModules->catalogTablename );

            if ( $objModules->type == 'catalogUniversalView' && $objModules->catalogTaxonomies ) {

                $arrTaxonomies = Toolkit::parseStringToArray( $objModules->catalogTaxonomies );

                if ( is_array( $arrTaxonomies ) && isset( $arrTaxonomies['query'] ) ) {

                    $arrQuery['where'] = Toolkit::parseQueries( $arrTaxonomies['query'] );
                }
            }

            $arrQuery['where'][] = [

                'field' => 'invisible',
                'operator' => 'not',
                'value' => '1'
            ];

            $strQuery = $strQuery . $this->SQLQueryBuilder->getWhereQuery( $arrQuery );
            $arrValues = $this->SQLQueryBuilder->getValues();

            if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerGetSearchablePagesQuery'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerGetSearchablePagesQuery'] ) ) {

                foreach ( $GLOBALS['TL_HOOKS']['catalogManagerGetSearchablePagesQuery'] as $arrCallback )  {

                    if ( is_array( $arrCallback ) ) {

                        $this->import( $arrCallback[0] );
                        $strQuery = $this->{$arrCallback[0]}->{$arrCallback[1]}( $strQuery, $arrValues, $objModules->row() );
                    }
                }
            }

            $objEntities = $this->Database->prepare( $strQuery )->execute( $this->SQLQueryBuilder->getValues() );

            if ( !$objEntities->numRows ) continue;

            $objCatalog = $this->Database->prepare( 'SELECT * FROM tl_catalog WHERE tablename = ?' )->limit(1)->execute( $objModules->catalogTablename );

            if ( !$objCatalog->numRows ) $objCatalog = null;

            while ( $objEntities->next() ) {

                $strSiteMapUrl = $this->createMasterUrl( $objCatalog, $objEntities, $strUrl );

                if ( $strSiteMapUrl && !in_array( $strSiteMapUrl, $arrPages ) ) $arrPages[] = $strSiteMapUrl;
            }
        }

        return $arrPages;
    }


    protected function createMasterUrl( $objCatalog, $objEntities, $strUrl ) {

        $strBase = '';
        $strUrl = rawurldecode( $strUrl );

        if ( !is_null( $objCatalog ) ) {

            if ( $objCatalog->useRedirect && $objCatalog->internalUrlColumn ) {

                if ( $objEntities->{$objCatalog->internalUrlColumn} ) {

                    $intPageID = intval( preg_replace('/[^0-9]+/', '', $objEntities->{$objCatalog->internalUrlColumn} ) );

                    $objParent = $this->getPageModelWithDetailsByID( $intPageID );
                    $strDomain = ( $objParent->rootUseSSL ? 'https://' : 'http://' ) . ( $objParent->domain ?: \Environment::get( 'host' ) ) . TL_PATH . '/';

                    return $strDomain . $this->generateFrontendUrl( $objParent->row() );
                }
            }

            if ( $objCatalog->useRedirect && $objCatalog->externalUrlColumn ) {

                if ( $objEntities->{$objCatalog->externalUrlColumn} ) {

                    return null;
                }
            }
        }

        return $strBase . sprintf( $strUrl, ( ( $objEntities->alias != '' && !\Config::get( 'disableAlias' ) ) ? $objEntities->alias : $objEntities->id ) );
    }


    protected function getPageModelWithDetailsByID( $intPageID ) {

        $dteTime = \Date::floorToMinute();
        $objPage = \PageModel::findWithDetails( $intPageID );

        if ( $objPage === null ) return null;

        if ( !$objPage->published || ( $objPage->start != '' && $objPage->start > $dteTime ) || ( $objPage->stop != '' && $objPage->stop <= ( $dteTime + 60 ) ) ) {

            return null;
        }

        if ( $objPage->sitemap == 'map_never' ) return null;

        return $objPage;
    }
}