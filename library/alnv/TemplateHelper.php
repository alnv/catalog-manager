<?php

namespace CatalogManager;

class TemplateHelper extends CatalogController {

    public function __construct() {

        parent::__construct();
    }

    public function addComments( $objTemplate, $arrConfig, $strTablename, $strID, $arrNotifies = [] ) {

        $this->import('Comments');

        $objCommentConfig = new \stdClass();

        if ( !empty( $arrConfig ) && is_array( $arrConfig ) ) {

            foreach ( $arrConfig as $strKey => $varValue ) {

                $objCommentConfig->{$strKey} = $varValue;
            }
        }

        $this->Comments->addCommentsToTemplate( $objTemplate, $objCommentConfig, $strTablename, $strID, $arrNotifies );
    }

    public function addPagination( $intTotal, $intPerPage, $strPageID, $pageID ) {

        $strPage = ( \Input::get( $strPageID ) !== null) ? \Input::get( $strPageID ) : 1;

        if ( $strPage < 1 || $strPage > max(ceil( $intTotal / $intPerPage ), 1 ) ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($pageID);
        }

        $objPagination = new \Pagination( $intTotal, $intPerPage, \Config::get('maxPaginationLinks'), $strPageID );

        return $objPagination->generate("\n  ");
    }
}