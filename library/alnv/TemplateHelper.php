<?php

namespace CatalogManager;

class TemplateHelper extends CatalogController {

    public function __construct() {

        parent::__construct();

        $this->import('Comments');
    }

    public function addComments( $objTemplate, $arrConfig, $strTablename, $strID, $arrNotifies = [] ) {

        $objCommentConfig = new \stdClass();

        if ( !empty( $arrConfig ) && is_array( $arrConfig ) ) {

            foreach ( $arrConfig as $strKey => $varValue ) {

                $objCommentConfig->{$strKey} = $varValue;
            }
        }

        $this->Comments->addCommentsToTemplate( $objTemplate, $objCommentConfig, $strTablename, $strID, $arrNotifies );
    }
}