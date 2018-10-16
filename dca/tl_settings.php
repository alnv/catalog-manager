<?php

$GLOBALS['TL_DCA']['tl_settings']['config']['onload_callback'] = [

    [ 'CatalogManager\tl_settings', 'changeGlobals' ]
];

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = str_replace( 'sslProxyDomain;', 'sslProxyDomain;{catalog_manager_settings},catalogLicence,catalogGoogleMapsServerKey,catalogGoogleMapsClientKey,catalogNavigationAreas;', $GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] );

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

        'tl_class' => 'w50'
    ],

    'save_callback' => [ [ 'CatalogManager\tl_settings', 'verifyLicence' ] ]
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['catalogNavigationAreas'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_settings']['catalogNavigationAreas'],
    'inputType' => 'keyValueWizard',

    'eval' => [

        'tl_class' => 'clr'
    ],

    'save_callback' => [ [ 'CatalogManager\tl_settings', 'parseNavigationName' ] ]
];