<?php

namespace CatalogManager;

class Toolkit {

    public static function parseStringToArray( $strValue ) {

        if ( $strValue && is_string( $strValue ) ) {

            return deserialize( $strValue );
        }

        if ( is_array( $strValue ) ) {

            return $strValue;
        }

        return [];
    }
}