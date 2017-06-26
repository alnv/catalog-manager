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


    public static function removeBreakLines( $strValue ) {

        if ( !$strValue || !is_string( $strValue ) ) {

            return $strValue;
        }

        return preg_replace( "/\r|\n/", "", $strValue );
    }


    public static function removeApostrophe( $strValue ) {

        if ( !$strValue || !is_string( $strValue ) ) {

            return $strValue;
        }

        return str_replace( "'", "", $strValue );
    }


    public static function parseConformSQLValue( $varValue ) {

        return str_replace( '-', '_', $varValue );
    }


    public static function prepareValues4Db( $arrValues ) {

        $arrReturn = [];

        if ( !empty( $arrValues ) && is_array( $arrValues ) ) {

            foreach ( $arrValues as $strKey => $varValue ) {

                $arrReturn[ $strKey ] = static::prepareValue4Db( $varValue );
            }
        }

        return $arrReturn;
    }


    public static function prepareValue4Db( $varValue ) {

        if ( !static::isDefined( $varValue ) ) return $varValue;

        if ( is_array( $varValue ) ) return implode( ',', $varValue );

        if ( is_float( $varValue ) ) return floatval( $varValue );

        return $varValue;
    }


    public static function prepareValueForQuery( $varValue ) {

        if ( !empty( $varValue ) && is_array( $varValue ) ) {

            $arrReturn = [];

            foreach ( $varValue as $strKey => $strValue ) {

                $arrReturn[ $strKey ] = Toolkit::prepareValueForQuery( $strValue );
            }

            return $arrReturn;
        }

        if ( is_numeric( $varValue ) ) {

            return floatval( $varValue );
        }

        if ( is_null( $varValue ) ) {

            return '';
        }
        
        return $varValue;
    }


    public static function deserialize( $strValue ) {

        $strValue = deserialize( $strValue );

        if ( !is_array( $strValue ) ) {

            return is_string( $strValue ) ? [ $strValue ] : [];
        }

        return $strValue;
    }


    public static function getBooleanByValue( $varValue ) {

        if ( !$varValue ) {

            return false;
        }

        return true;
    }


    public static function deserializeAndImplode( $strValue, $strDelimiter = ',' ) {

        if ( !$strValue || !is_string( $strValue ) ) {

            return '';
        }

        $arrValue = deserialize( $strValue );

        if ( !empty( $arrValue ) && is_array( $arrValue ) ) {

            return implode( $strDelimiter, $arrValue );
        }

        return '';
    }


    public static function isDefined( $varValue ) {

        if ( is_numeric( $varValue ) ) {

            return true;
        }

        if ( is_array( $varValue )) {

            return true;
        }

        if ( $varValue && is_string( $varValue ) ) {

            return true;
        }

        return false;
    }


    public static function parseColumns( $arrColumns ) {

        $arrReturn = [];

        if ( !empty( $arrColumns ) && is_array( $arrColumns ) ) {

            foreach ( $arrColumns as $arrColumn ) {

                if ( $arrColumn['name'] == 'PRIMARY' ) {

                    continue;
                }

                $arrReturn[ $arrColumn['name'] ] = $arrColumn['name'];
            }
        }

        return $arrReturn;
    }


    public static function parseQueries( $arrQueries, $fnCallback = null ) {

        $arrReturn = [];

        if ( !empty( $arrQueries ) && is_array( $arrQueries ) ) {

            foreach ( $arrQueries as $arrQuery ) {

                if ( is_null( $arrQuery['value'] ) || $arrQuery['value'] === '' ) {

                    return null;
                }

                if ( !is_null( $fnCallback ) && is_callable( $fnCallback ) ) {

                    $arrQuery = $fnCallback( $arrQuery );
                }

                $arrQuery = static::parseQuery( $arrQuery );

                if ( is_null( $arrQuery ) ) continue;

                if ( !empty( $arrQuery['subQueries'] ) && is_array( $arrQuery['subQueries'] ) ) {

                    $arrSubQueries = static::parseQueries( $arrQuery['subQueries'] );

                    array_insert( $arrSubQueries, 0, [[

                        'field' => $arrQuery['field'],
                        'value' => $arrQuery['value'],
                        'operator' => $arrQuery['operator'],
                    ]]);

                    $arrReturn[] = $arrSubQueries;
                }

                else {

                    $arrReturn[] = $arrQuery;
                }
            }
        }

        return $arrReturn;
    }


    public static function parseQuery( $arrQuery ) {

        $blnAllowEmptyValue = $arrQuery['allowEmptyValues'] ? true : false;

        if ( is_array( $arrQuery['value'] ) ) {

            if ( !empty( $arrQuery['value'] ) ) {

                foreach ( $arrQuery['value'] as $strK => $strV ) {

                    $arrQuery['value'][ $strK ] = \Controller::replaceInsertTags( $strV );
                }
            }

            if ( $arrQuery['operator'] == 'between' ) {

                if ( $arrQuery['value'][0] === '' || $arrQuery['value'][1] === '' ) {

                    return null;
                }
            }
        }

        if ( is_string( $arrQuery['value'] ) ) {

            $arrQuery['value'] = \Controller::replaceInsertTags( $arrQuery['value'] );

            if ( strpos( $arrQuery['value'], ',' ) ) {

                $arrQuery['value'] = explode( ',' , $arrQuery['value'] );
            }
        }

        if ( is_array( $arrQuery['value'] ) && !in_array( $arrQuery['operator'], [ 'contain', 'between' ] ) ) {

            $arrQuery['multiple'] = true;
        }

        if ( ( is_null( $arrQuery['value'] ) || $arrQuery['value'] === '') && !$blnAllowEmptyValue ) {

            return null;
        }

        $arrQuery['value'] = static::prepareValueForQuery( $arrQuery['value'] );

        return $arrQuery;
    }


    public static function returnOnlyExistedItems( $arrItems, $arrExistedFields, $blnKeysOnly = false ) {

        $arrReturn = [];
        $arrExistedValues = $blnKeysOnly ? array_keys( $arrExistedFields ) : $arrExistedFields;

        if ( !empty( $arrItems ) && is_array( $arrItems ) ) {

            foreach ( $arrItems as $varValue ) {

                if ( !$varValue || !is_string( $varValue ) ) continue;

                if ( !in_array( $varValue, $arrExistedValues ) ) continue;

                $arrReturn[] = $varValue;
            }
        }

        return $arrReturn;
    }


    public static function getRoutingParameter( $strRoutingParameter, $blnEmptyArray = false ) {

        $arrReturn = [];
        $arrRoutingFragments = explode( '/' , $strRoutingParameter );
        
        if ( !empty( $arrRoutingFragments ) && is_array( $arrRoutingFragments ) ) {

            foreach ( $arrRoutingFragments as $strRoutingFragment ) {

                if ( !$strRoutingFragment ) continue;

                preg_match_all( '/{(.*?)}/', $strRoutingFragment, $arrMatches );

                $strParamName = implode( '', $arrMatches[1] );

                if ( $strParamName ) {

                    $arrReturn[ $strParamName ] = $blnEmptyArray ? [] : $strParamName;
                }
            }
        }

        return $arrReturn;
    }


    public static function parseMultipleOptions( $varValue ) {

        if ( is_string( $varValue ) ) {

            $varValue = explode( ',', $varValue );
        }

        return $varValue;
    }
}