<?php

namespace CatalogManager;

class DcModifier extends CatalogController {

    protected $arrFields = [];
    protected $arrPalettes = [];
    protected $strTablename = '';


    public function __construct() {

        parent::__construct();

        $this->import( 'I18nCatalogTranslator' );
    }


    public function initialize( $strTablename ) {

        $this->strTablename = $strTablename;

        \Controller::loadLanguageFile( $this->strTablename );
        \Controller::loadDataContainer( $this->strTablename );

        $this->I18nCatalogTranslator->initialize();

        if ( !empty( $GLOBALS['TL_DCA'][ $this->strTablename ]['fields'] ) && is_array( $GLOBALS['TL_DCA'][ $this->strTablename ]['fields'] ) ){

            $this->arrFields = array_keys( $GLOBALS['TL_DCA'][ $this->strTablename ]['fields'] ) ?: [];
        }

        if ( !empty( $GLOBALS['TL_DCA'][ $this->strTablename ]['palettes'] ) && is_array( $GLOBALS['TL_DCA'][ $this->strTablename ]['palettes'] ) ) {

            $this->arrPalettes = array_keys( $GLOBALS['TL_DCA'][ $this->strTablename ]['palettes'] ) ?: [];
        }
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


    public function addFieldToPalette( $arrField, $arrPickedPalettes, &$arrPalettes = [] ) {

        foreach ( $arrPickedPalettes as $arrPickedPalette ) {

            $strField = $arrPickedPalette['value'];
            $strPalette = $arrPickedPalette['key'];
            $strFieldname = $arrField['fieldname'];

            if ( !$strField || !$strPalette ) continue;

            $strModifiedPalette = '';
            $arrFieldsPlucked = preg_split( '/(,|;)/', $arrPalettes[ $strPalette ] );

            foreach ( $arrFieldsPlucked as $intIndex => $strFieldPlucked ) {

                $blnEnding = false;

                if ( Toolkit::isEmpty( $strFieldPlucked ) ) continue;

                if ( isset( $arrFieldsPlucked[ $intIndex +1 ] ) && $this->isLegend( $arrFieldsPlucked[ $intIndex +1 ] ) ) $blnEnding = true;

                if ( $strFieldPlucked != $strFieldname ) $strModifiedPalette .= $strFieldPlucked;

                if ( $strField == $strFieldPlucked ) $strModifiedPalette .= ',' . $strFieldname;

                $strModifiedPalette .= ( $blnEnding ? ';' : ',' );
            }

            $arrPalettes[ $strPalette ] = $strModifiedPalette;
        }

        return $arrPalettes;
    }


    public function addLegendToPalette( $arrFields, $arrPickedPalettes, &$arrPalettes = [], $arrFieldsetStart ) {

        foreach ( $arrPickedPalettes as $arrPickedPalette ) {

            $strPalette = $arrPickedPalette['key'];
            $strLegend = $arrPickedPalette['value'];

            if ( !$strLegend || !$strPalette ) continue;

            $arrModifiedPalettes = [];
            $arrPalettesPlucked = explode( ';', $arrPalettes[ $strPalette ] );

            foreach ( $arrPalettesPlucked as $strFieldset ) {

                if ( Toolkit::isEmpty( $strFieldset ) ) continue;

                $arrModifiedPalettes[] = $strFieldset;

                if ( $arrMatch = $this->isLegend( $strFieldset, true ) ) {

                    $arrLegendName = isset( $arrMatch[1] ) ? $arrMatch[1][0] : '';
                    $arrLegendName = explode( ':', $arrLegendName );

                    if ( $arrLegendName[0] ==  $strLegend ) {

                        $arrModifiedPalettes[] = '{' . $arrFieldsetStart['title'] . ( $arrFieldsetStart['isHidden'] ? ':hide' : '' ) . '},' . implode( ',' , $arrFields );
                        $GLOBALS['TL_LANG'][ $this->strTablename ][ $arrFieldsetStart['title'] ] = $this->I18nCatalogTranslator->get( 'legend', $arrFieldsetStart['title'], [ 'title' => $arrFieldsetStart[ 'label' ] ] );
                    }
                }
            }

            $arrPalettes[ $strPalette ] = implode( ';', $arrModifiedPalettes );
        }

    }


    protected function isLegend( $strValue, $blnReturnMatch = false ) {

        preg_match( '/{(([^{}]*|(?R))*)}/', $strValue, $arrMatch, PREG_OFFSET_CAPTURE, 0 );

        if ( $blnReturnMatch ) return $arrMatch;

        return !empty( $arrMatch );
    }
}