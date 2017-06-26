<?php

namespace CatalogManager;

class CatalogInput extends CatalogController {


    public function __construct() {

        parent::__construct();

        $this->import( 'Input' );
    }


    protected function getPostCookie( $strName ) {

        $strReturn = '';
        $strPost = $this->Input->post( $strName );

        if ( $this->Input->post( 'FORM_SUBMIT' ) == 'tl_filter' ) {

            $strCookie = $strPost;

            if ( !is_null( $strCookie ) && $strCookie != '' ) $strCookie = serialize( $strPost );

            \System::setCookie( $strName, $strCookie, time() + 3000  );

            $strReturn = $strPost;
        }

        if ( ( is_null( $strReturn ) || $strReturn === '' ) && $this->Input->post( 'FORM_SUBMIT' ) != 'tl_filter' ) {

            $strReturn = $this->Input->cookie( $strName );

            if ( !is_null( $strReturn ) && $strReturn != '' ) {

                $strReturn = unserialize( $strReturn );
            }

            if ( is_bool( $strReturn ) ) {

                $strReturn = $strReturn ? '1' : '0';
            }
        }

        return $strReturn;
    }


    public function post( $strName ) {

        $strPostCookie = $this->getPostCookie( $strName );

        if ( !is_null( $strPostCookie ) && $strPostCookie != '' ) {

            return $strPostCookie;
        }

        return '';
    }

    public function get( $strName ) {

        $strGet = $this->Input->get( $strName );

        if ( !is_null( $strGet ) && $strGet != '' ) {

            return $strGet;
        }

        return '';
    }


    public function getActiveValue( $strName ) {

        if ( $this->get( $strName ) != '' ) {

            return $this->get( $strName );
        }

        $strPost = $this->post( $strName );

        if ( $strPost != '' ) {

            return $strPost;
        }

        return '';
    }
}