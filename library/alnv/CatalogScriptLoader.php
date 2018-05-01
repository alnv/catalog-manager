<?php

namespace CatalogManager;

class CatalogScriptLoader {


    protected $arrScripts = [

        'awesomplete-frontend' => [

            [
                'debug' => '/system/modules/catalog-manager/assets/awesomplete/awesomplete.js',
                'compressed' => '/system/modules/catalog-manager/assets/awesomplete/awesomplete.min.js'
            ],

            [
                'debug' => '/system/modules/catalog-manager/assets/awesomplete/awesomplete.setup.frontend.js',
                'compressed' => '/system/modules/catalog-manager/assets/awesomplete/awesomplete.setup.frontend.js'
            ]
        ],

        'awesomplete-backend' => [

            [
                'debug' => '/system/modules/catalog-manager/assets/awesomplete/awesomplete.js',
                'compressed' => '/system/modules/catalog-manager/assets/awesomplete/awesomplete.min.js'
            ],

            [
                'debug' => '/system/modules/catalog-manager/assets/awesomplete/awesomplete.setup.backend.js',
                'compressed' => '/system/modules/catalog-manager/assets/awesomplete/awesomplete.setup.backend.js'
            ]
        ]
    ];


    protected $arrStyles = [

        'awesomplete' => [

            [
                'debug' => '/system/modules/catalog-manager/assets/awesomplete/awesomplete.css',
                'compressed' => '/system/modules/catalog-manager/assets/awesomplete/awesomplete.css'
            ]
        ]
    ];


    public function loadScript( $strScriptName, $strType = 'TL_HEAD' ) {

        if ( isset( $this->arrScripts[ $strScriptName ] ) && is_array( $this->arrScripts[ $strScriptName ] ) ) {

            switch ( $strType ) {

                case 'TL_HEAD':

                    foreach ( $this->arrScripts[ $strScriptName ] as $intIndex => $arrScript ) {

                        $GLOBALS['TL_HEAD']['catalog.js.' . $strScriptName . '.' . $intIndex  ] = $GLOBALS['TL_CONFIG']['debugMode'] ? '<script src="' . $arrScript['debug'] . '"></script>' : '<script src="'. $arrScript['compressed'] .'"></script>';
                    }

                    break;

                case 'TL_JAVASCRIPT':

                    foreach ( $this->arrScripts[ $strScriptName ] as $intIndex => $arrScript ) {

                        $GLOBALS['TL_JAVASCRIPT']['catalog.js.' . $strScriptName . '.' . $intIndex  ] = $GLOBALS['TL_CONFIG']['debugMode'] ? $arrScript['debug'] : $arrScript['compressed'];
                    }

                    break;
            }
        }
    }


    public function loadStyle( $strStyleName, $strType = 'TL_HEAD' ) {

        if ( isset( $this->arrStyles[ $strStyleName ] ) && is_array( $this->arrStyles[ $strStyleName ] ) ) {

            switch ( $strType ) {

                case 'TL_HEAD':

                    foreach ( $this->arrStyles[ $strStyleName ] as $intIndex => $arrStyle ) {

                        $GLOBALS['TL_HEAD']['catalog.css.' . $strStyleName . '.' . $intIndex  ] = $GLOBALS['TL_CONFIG']['debugMode'] ? '<link href="'. $arrStyle['debug'] .'" rel="stylesheet" type="text/css"></link>' : '<link href="'. $arrStyle['compressed'] .'" rel="stylesheet" type="text/css"></link>';
                    }

                    break;

                case 'TL_CSS':

                    foreach ( $this->arrStyles[ $strStyleName ] as $intIndex => $arrStyle ) {

                        $GLOBALS['TL_CSS']['catalog.css.' . $strStyleName . '.' . $intIndex  ] = $GLOBALS['TL_CONFIG']['debugMode'] ? $arrStyle['debug'] : $arrStyle['compressed'];
                    }

                    break;
            }
        }
    }
}