<?php

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = str_replace( 'sslProxyDomain;', 'sslProxyDomain;{catalog_manager_settings},catalogLicence,catalogGoogleMapsServerKey,catalogGoogleMapsClientKey;', $GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] );

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

$GLOBALS['TL_DCA']['tl_settings']['fields']['catalogLicence'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_settings']['catalogLicence'],
    'inputType' => 'text',

    'eval' => [

        'tl_class' => 'w50 long'
    ]
];