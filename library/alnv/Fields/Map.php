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

    private static function prepareMapOptions( $arrField, $arrCatalog ) {

        return [

            'catalog' => $arrCatalog,
            'title' => $arrField['title'],
            'fieldname' => $arrField['fieldname'],
            'description' => $arrField['description'],
            'mapTemplate' => $arrField['mapTemplate'],
            'lat' => $arrCatalog[ $arrField['latField'] ],
            'lng' => $arrCatalog[ $arrField['lngField'] ],
            'id' => static::createUniqueID( $arrField, $arrCatalog ),
            'mapMarker' => $arrField['mapMarker'] ? 'true' : 'false',
            'mapInfoBox' => $arrField['mapInfoBox'] ? 'true' : 'false',
            'mapStyle' => $arrField['mapStyle'] ? $arrField['mapStyle'] : '',
            'mapScrollWheel' => $arrField['mapScrollWheel'] ? 'true' : 'false',
            'mapType' => $arrField['mapType'] ? $arrField['mapType'] : 'HYBRID',
            'mapZoom' => $arrField['mapZoom'] ? intval( $arrField['mapZoom'] ) : 10
        ];
    }

    private static function createUniqueID( $arrField, $arrCatalog ) {

        return 'map_' . $arrField['fieldname'] . '_' . $arrCatalog['id'];
    }

    public static function generateGoogleMapJSInitializer() {

        $strScript = sprintf( "http%s://maps.google.com/maps/api/js?language=%s%s", ( \Environment::get('ssl') ? 's' : '' ), 'en', ( \Config::get('catalogGoogleMapsClientKey') ? '&key='. \Config::get('catalogGoogleMapsClientKey') .'' : '' ) );

        return ''.

        '<script async defer>'.
            '(function(){'.

                '"use strict";'.

                'function loadGoogleMaps() {'.
                    ' var objJSScript=document.createElement("script");'.
                    ' objJSScript.src="' . $strScript . '";'.
                    ' objJSScript.onload=loadGoogleMapsInfoBoxLibrary;'.
                    ' document.body.appendChild( objJSScript );'.
                '};'.

                'function loadGoogleMapsInfoBoxLibrary() {'.
                    ' var objJSScript=document.createElement("script");'.
                    ' objJSScript.src="system/modules/catalog-manager/assets/InfoBox.js";'.
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