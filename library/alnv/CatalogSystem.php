<?php

namespace CatalogManager;

class CatalogSystem {

    private $arrObjects = [];

    public function __get( $strKey ) {

        return $this->arrObjects[ $strKey ];
    }

    protected function import( $strClass, $strKey = null, $blnForce = false ) {

        $strKey = $strKey ?: $strClass;

        if ( $blnForce || !isset( $this->arrObjects[ $strKey ] ) ) {

            $this->arrObjects[ $strKey ] = ( in_array( 'getInstance', get_class_methods( $strClass ) ) ) ? call_user_func( [ $strClass, 'getInstance' ] ) : new $strClass();
        }
    }
}