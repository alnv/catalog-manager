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

        if ( is_numeric( $varValue ) ) return intval( $varValue );

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

            return [];
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


    public static function parseWhereQueryArray( $arrQueries, $fnCallback = null ) {

        $intIndex = 0;
        $arrReturn = [];

        if ( !empty( $arrQueries ) && is_array( $arrQueries ) ) {

            foreach ( $arrQueries as $arrQuery ) {

                if ( $arrQuery['subQueries'] && is_array( $arrQuery['subQueries'] ) ) {

                    $arrReturn[ $intIndex ][] = [

                        'field' => $arrQuery['field'],
                        'value' => $arrQuery['value'],
                        'operator' => $arrQuery['operator'],
                    ];

                    foreach ( $arrQuery['subQueries'] as $arrSubQuery ) {

                        if ( !is_null( $fnCallback ) && is_callable( $fnCallback ) ) {

                            $arrSubQuery = $fnCallback( $arrSubQuery );
                        }

                        if ( is_null( $arrSubQuery ) ) continue;

                        $arrReturn[$intIndex][] = $arrSubQuery;
                    }

                    $intIndex++;
                }

                else {

                    if ( !is_null( $fnCallback ) && is_callable( $fnCallback ) ) {

                        $arrQuery = $fnCallback( $arrQuery );
                    }

                    if ( is_null( $arrQuery ) ) continue;

                    $arrReturn[] = $arrQuery;
                }
            }
        }
        
        return $arrReturn;
    }


    public static function getZoomFactor( $intDistance ) {

        if ( $intDistance > 0 && $intDistance < 50 ) {

            return 16;
        }

        if ( $intDistance > 50 && $intDistance <= 100 ) {

            return 13;
        }

        if ( $intDistance > 100 && $intDistance <= 150 ) {

            return 11;
        }

        if ( $intDistance > 150 && $intDistance <= 500 ) {

            return 7;
        }

        if ( $intDistance > 500 && $intDistance <= 1000 ) {

            return 6;
        }

        if ( $intDistance > 1000 && $intDistance <= 1500 ) {

            return 5;
        }

        if ( $intDistance > 2000 && $intDistance <= 3000 ) {

            return 4;
        }

        return 2;
    }
}