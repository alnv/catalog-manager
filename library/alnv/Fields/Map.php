<?php

namespace CatalogManager;

class Map {


    public static function generate( $arrDCAField, $arrField ) {

        return $arrDCAField;
    }


    public static function parseValue( $varValue, $arrField, $arrCatalog = [] ) {

        $arrMapOptions = static::prepareMapOptions( $arrField, $arrCatalog );

        $objTemplate = new \FrontendTemplate( $arrMapOptions['mapTemplate'] );
        $objTemplate->setData( $arrMapOptions );

        return $objTemplate->parse();
    }


    public static function prepareMapOptions( $arrField, $arrCatalog ) {

        $arrLatField = $arrCatalog[ $arrField['latField'] ];
        $arrLngField = $arrCatalog[ $arrField['lngField'] ];

        if ( ( !$arrLatField && !$arrLngField ) && $arrField['fieldname'] ) {

            $objDatabase = \Database::getInstance();
            $objCatalog = $objDatabase->prepare( 'SELECT * FROM tl_catalog WHERE id = ( SELECT pid FROM tl_catalog_fields WHERE fieldname = ? LIMIT 1 )' )->limit( 1 )->execute( $arrField['fieldname'] );

            if ( $objCatalog->tablename ) {

                $strLatField = $objCatalog->tablename . ucfirst( $arrField['latField'] );
                $strLngField = $objCatalog->tablename . ucfirst( $arrField['lngField'] );

                $arrLatField = $arrCatalog[ $strLatField ];
                $arrLngField = $arrCatalog[ $strLngField ];
            }
        }

        $arrReturn = [

            'lat' => $arrLatField,
            'lng' => $arrLngField,
            'catalog' => $arrCatalog,
            'mapInfoBoxContent' => '',
            'title' => $arrField['title'],
            'fieldname' => $arrField['fieldname'],
            'description' => $arrField['description'],
            'mapTemplate' => $arrField['mapTemplate'],
            'id' => static::createUniqueID( $arrField, $arrCatalog ),
            'mapMarker' => $arrField['mapMarker'] ? 'true' : 'false',
            'addMapInfoBox' => $arrField['addMapInfoBox'] ? 'true' : 'false',
            'mapStyle' => $arrField['mapStyle'] ? $arrField['mapStyle'] : '',
            'mapScrollWheel' => $arrField['mapScrollWheel'] ? 'true' : 'false',
            'mapType' => $arrField['mapType'] ? $arrField['mapType'] : 'HYBRID',
            'mapZoom' => $arrField['mapZoom'] ? intval( $arrField['mapZoom'] ) : 10
        ];

        if ( $arrField['mapInfoBoxContent'] ) {

            $arrReturn['mapInfoBoxContent'] = static::parseInfoBoxContent( $arrField['mapInfoBoxContent'], $arrCatalog );
        }

        return $arrReturn;
    }


    public static function parseInfoBoxContent( $strInfoBox, $arrData ) {

        $strInfoBox = \StringUtil::parseSimpleTokens( $strInfoBox, $arrData );
        $strInfoBox = Toolkit::removeBreakLines( $strInfoBox );
        $strInfoBox = Toolkit::removeApostrophe( $strInfoBox );

        return \StringUtil::toHtml5( $strInfoBox );
    }

    
    public static function getMapViewOptions( $arrOptions ) {

        $arrOptions['mapMarker'] = $arrOptions['mapMarker'] ? 'true' : 'false';
        $arrOptions['mapZoom'] = $arrOptions['mapZoom'] ? $arrOptions['mapZoom'] : 10;
        $arrOptions['addMapInfoBox'] = $arrOptions['addMapInfoBox'] ? 'true' : 'false';
        $arrOptions['mapScrollWheel'] = $arrOptions['mapScrollWheel'] ? 'true' : 'false';
        $arrOptions['mapType'] = $arrOptions['mapType'] ? $arrOptions['mapType'] : 'HYBRID';

        return $arrOptions;
    }


    private static function createUniqueID( $arrField, $arrCatalog ) {

        return 'map_' . $arrField['fieldname'] . '_' . $arrCatalog['id'];
    }


    public static function generateGoogleMapJSInitializer() {

        $strScript = sprintf( "http%s://maps.google.com/maps/api/js?language=%s%s", ( \Environment::get('ssl') ? 's' : '' ), ( $GLOBALS['TL_LANGUAGE'] ? $GLOBALS['TL_LANGUAGE'] : 'en' ), ( \Config::get('catalogGoogleMapsClientKey') ? '&key='. \Config::get('catalogGoogleMapsClientKey') .'' : '' ) );

        return ''.

        '<script async defer>'.
            '(function(){'.

                '"use strict";'.

                'function loadGoogleMaps() {'.
                    ' var objJSScript=document.createElement("script");'.
                    ' objJSScript.src="' . $strScript . '";'.
                    ' objJSScript.id="id_ctlg_gm_api";'.
                    ' objJSScript.onload=loadGoogleMapsInfoBoxLibrary;'.
                    ' document.body.appendChild( objJSScript );'.
                '};'.

                'function loadGoogleMapsInfoBoxLibrary() {'.
                    ' var objJSScript=document.createElement("script");'.
                    ' objJSScript.src="system/modules/catalog-manager/assets/InfoBox.js";'.
                    ' objJSScript.id="id_ctlg_ib";'.
                    ' objJSScript.onload=loadCatalogManagerMaps;'.
                    ' document.body.appendChild( objJSScript );'.
                '};'.

                'function loadCatalogManagerMaps() {'.
                    'if ( typeof CatalogManagerMaps != "undefined" ) {'.
                       'if ( typeof CatalogManagerMaps == "object" && CatalogManagerMaps.length ) {'.
                            'for( var i = 0; i < CatalogManagerMaps.length; i++ ){ CatalogManagerMaps[i](); }'.
                        '}'.
                    '}'.
                '};'.

                'if ( document.addEventListener ){ document.addEventListener( "DOMContentLoaded", loadGoogleMaps, false ); } else if ( document.attachEvent ){ document.attachEvent( "onload", loadGoogleMaps ); }'.
            '})()'.
        '</script>';
    }
}