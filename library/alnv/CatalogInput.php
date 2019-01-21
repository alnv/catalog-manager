<?php

namespace CatalogManager;

class CatalogInput extends CatalogController {


    protected $strFormId;


    public function __construct() {

        parent::__construct();

        $this->strFormId = md5( 'tl_filter' );
        $this->import( 'Input' );
    }


    protected function getPostCookie( $strName ) {

        $objSession = \Session::getInstance();
        $strActiveValue = $this->Input->post( $strName );
        $arrEditingMode = preg_grep ( '/^act(\d+)/i', array_keys( $_GET ) );
        $arrPagination = preg_grep ( '/^page_e(\d+)/i', array_keys( $_GET ) );

        if ( $this->Input->post( 'FORM_SUBMIT' ) == $this->strFormId ) $objSession->set( $strName, $strActiveValue );

        if ( !empty( $arrPagination ) || ( Toolkit::isEmpty( $strActiveValue ) && !Toolkit::isEmpty( $objSession->get( $strName ) ) ) ) {

            if ( TL_MODE == 'FE' ) {

                $strActiveValue = $objSession->get( $strName );
            }
        }

        if ( !empty( $arrEditingMode ) ) {

            $strActiveValue = $this->Input->post( $strName );
        }

        return $strActiveValue;
    }


    public function post( $strName ) {

        $strPostCookie = $this->getPostCookie( $strName );

        if ( !is_null( $strPostCookie ) && $strPostCookie != '' ) return $this->parseValue( $strPostCookie );

        return '';
    }

    
    public function get( $strName ) {

        $strGet = $this->Input->get( $strName );

        if ( !is_null( $strGet ) && $strGet != '' ) return $this->parseValue( $strGet );

        return '';
    }


    public function getActiveValue( $strName ) {

        if ( $this->get( $strName ) != '' ) return $this->get( $strName );
        
        $strPost = $this->post( $strName );

        if ( $strPost != '' ) return $strPost;

        return '';
    }


    public function getValue( $strName ) {

        if ( $this->get( $strName ) != '' ) return $this->get( $strName );

        $strPost = $this->Input->post( $strName );

        if ( !Toolkit::isEmpty( $strPost ) ) return $strPost;

        return '';
    }


    protected function parseValue( $varValues ) {

        if ( is_array( $varValues ) && !empty( $varValues ) ) {

            foreach ( $varValues as $intIndex => $strValue ) {

                $varValues[ $intIndex ] = \StringUtil::decodeEntities( $strValue );
            }

            return $varValues;
        }

        if ( is_string( $varValues ) && $varValues != '' ) {

            return \StringUtil::decodeEntities( $varValues );
        }

        return $varValues;
    }
}