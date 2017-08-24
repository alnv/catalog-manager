<?php

namespace CatalogManager;

class DcModifier extends CatalogController {

    protected $arrFields = [];
    protected $arrPalettes = [];
    protected $strTablename = '';


    public function __construct() {

        parent::__construct();
    }


    public function initialize( $strTablename ) {

        $this->strTablename = $strTablename;

        \Controller::loadLanguageFile( $this->strTablename );
        \Controller::loadDataContainer( $this->strTablename );

        $this->arrFields = array_keys( $GLOBALS['TL_DCA'][ $this->strTablename ]['fields'] ) ?: [];
        $this->arrPalettes = array_keys( $GLOBALS['TL_DCA'][ $this->strTablename ]['palettes'] ) ?: [];
    }


    public function getPalettes() {

        $arrReturn = [];

        if ( is_array( $this->arrPalettes ) ) {

            foreach ( $this->arrPalettes as $strPalette ) {

                if ( $strPalette == '__selector__' ) continue;

                $arrReturn[ $strPalette ] = $strPalette;
            }
        }

        return $arrReturn;
    }


    public function getFields( $strName ) {

        $arrReturn = [];

        if ( !$strName ) return $arrReturn;

        $strPalette = $GLOBALS['TL_DCA'][ $this->strTablename ]['palettes'][ $strName ];
        $arrFields = preg_split( '/(,|;)/', $strPalette );

        if ( !empty( $arrFields ) && is_array( $arrFields ) ) {

            foreach ( $arrFields as $strField ) {

                if ( in_array( $strField, $this->arrFields ) ) {

                    $strLabel = $strField;

                    if ( is_array( $GLOBALS['TL_LANG'][ $this->strTablename ][ $strField ] ) ) {

                        $strLabel = $GLOBALS['TL_LANG'][ $this->strTablename ][ $strField ][0] ?: $strLabel;
                    }
                    
                    $arrReturn[ $strField ] = $strLabel;
                }
            }
        }

        return $arrReturn;
    }


    public function getLegends( $strName ) {

        $arrReturn = [];

        if ( !$strName ) return $arrReturn;

        $strPalette = $GLOBALS['TL_DCA'][ $this->strTablename ]['palettes'][ $strName ];

        if ( $strPalette ) {

            $arrLegends = explode( ';', $strPalette );

            if ( is_array( $arrLegends ) && !empty( $arrLegends ) ) {

                foreach ( $arrLegends as $strLegend ) {

                    $strLegendName = '';
                    preg_match( '/{(([^{}]*|(?R))*)}/', $strLegend, $arrMatch, PREG_OFFSET_CAPTURE, 0 );

                    if ( isset( $arrMatch[1] ) && is_array( $arrMatch[1] ) ) {
                        
                        $strLegendName = $arrMatch[1][0] ?: '';
                    }

                   if ( $strLegendName ) {

                       $arrLegendName = explode( ':' , $strLegendName );
                       $strLabel = $GLOBALS['TL_LANG'][ $this->strTablename ][ $arrLegendName[0] ];
                       
                       $arrReturn[ $strLegendName ] = $strLabel ? $strLabel : $arrLegendName[0];
                   }
                }
            }
        }

        return $arrReturn;
    }
}