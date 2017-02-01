<?php

namespace CatalogManager;

class FrontendEditingPermission extends CatalogController {

    public function __construct() {

        parent::__construct();

        $this->import( 'FrontendUser', 'User' );
    }

    public function initialize() {

        $this->setAttributes();
    }

    public function isAdmin() {

        return false;
    }

    public function hasAccess( $strMode, $strField ) {

        return false;
    }

    public function isOwnEntity( $strUserID ) {

        return false;
    }

    private function setAttributes() {

        //
    }
}