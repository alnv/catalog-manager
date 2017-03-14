<?php

namespace CatalogManager;

class SearchIndexBuilder extends \Frontend {


    public function initialize( $arrPages, $intRoot = 0, $blnIsSitemap = false ) {

        $arrRoot = [];

        if ( $intRoot > 0 ) {

            $arrRoot = $this->Database->getChildRecords( $intRoot, 'tl_page' );
        }

        $arrProcessed = [];
        $dteTime = \Date::floorToMinute();
        $objModules = $this->Database->prepare( 'SELECT * FROM tl_module WHERE type = ? OR type = ?' )->execute( 'catalogUniversalView', 'catalogMasterView' );

        while ( $objModules->next() ) {

            if ( !$objModules->catalogUseMasterPage ) continue;

            if ( !$objModules->catalogMasterPage ) continue;

            if ( !$objModules->catalogTablename ) continue;

            if ( !empty( $arrRoot ) && !in_array( $objModules->catalogMasterPage, $arrRoot ) ) continue;

            if ( !isset( $arrProcessed[ $objModules->catalogMasterPage ] ) ) {

                $objParent = \PageModel::findWithDetails( $objModules->catalogMasterPage );

                if ( $objParent === null ) continue;

                if ( !$objParent->published || ( $objParent->start != '' && $objParent->start > $dteTime ) || ( $objParent->stop != '' && $objParent->stop <= ( $dteTime + 60 ) ) ) {

                    continue;
                }

                if ( $objParent->sitemap == 'map_never' ) continue;

                $strDomain = ( $objParent->rootUseSSL ? 'https://' : 'http://' ) . ( $objParent->domain ?: \Environment::get( 'host' ) ) . TL_PATH . '/';
                $arrProcessed[ $objModules->catalogMasterPage ] = $strDomain . $this->generateFrontendUrl( $objParent->row(), ( ( \Config::get( 'useAutoItem' ) && !\Config::get( 'disableAlias' ) ) ? '/%s' : '/items/%s' ), $objParent->language );
            }

            $strUrl = $arrProcessed[ $objModules->catalogMasterPage ];
            $objEntities = $this->Database->prepare( sprintf( 'SELECT * FROM %s', $objModules->catalogTablename ) )->execute(); // todo taxonomies

            if ( !$objEntities->numRows ) continue;

            while ( $objEntities->next() ) {

                $arrPages[] = Toolkit::getLink( $objEntities, $strUrl );
            }
        }

        return $arrPages;
    }
}