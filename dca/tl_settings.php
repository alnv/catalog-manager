<?php

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = str_replace( 'sslProxyDomain;', 'sslProxyDomain;{catalog_manager_google_maps_settings},catalogGoogleMapsServerKey,catalogGoogleMapsServerKey;', $GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] );

$GLOBALS['TL_DCA']['tl_settings']['fields']['catalogGoogleMapsServerKey'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_settings']['catalogGoogleMapsServerKey'],
    'inputType' => 'text',
    'eval' => [

        'tl_class' => 'w50'
    ]
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['catalogGoogleMapsClientKey'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_settings']['catalogGoogleMapsClientKey'],
    'inputType' => 'text',
    'eval' => [

        'tl_class' => 'w50'
    ]
];