<?php

namespace CatalogManager;

class CatalogException {


    public function set404() {

        if ( version_compare( VERSION, '4.0', '>=' ) ) {

            throw new \CoreBundle\Exception\PageNotFoundException( 'Page not found: ' . \Environment::get('uri') );
        }

        else {

            global $objPage;

            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate( $objPage->id );
        }
    }


    public function set403() {

        if ( version_compare( VERSION, '4.0', '>=' ) ) {

            throw new \CoreBundle\Exception\AccessDeniedException( 'Page access denied:  ' . \Environment::get('uri') );
        }

        else {

            global $objPage;

            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate( $objPage->id );
        }
    }
}