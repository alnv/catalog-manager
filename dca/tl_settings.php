<?php

$GLOBALS['TL_DCA']['tl_settings']['config']['onload_callback'] = [
    [ 'CatalogManager\tl_settings', 'changeGlobals' ]
];

$GLOBALS['TL_DCA']['tl_settings']['palettes']['__selector__'][] = 'catalogMapProtected';
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{catalog_manager_settings},catalogLicence,catalogGoogleMapsServerKey,catalogGoogleMapsClientKey,catalogNavigationAreas;{catalog_manager_dsgvo_legend},catalogMapProtected';
$GLOBALS['TL_DCA']['tl_settings']['subpalettes']['catalogMapProtected'] = 'catalogMapPrivacyText,catalogMapPrivacyButtonText';

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
    'save_callback' => [['CatalogManager\tl_settings', 'parseNavigationName']]
];
$GLOBALS['TL_DCA']['tl_settings']['fields']['catalogMapProtected'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['catalogMapProtected'],
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr',
        'submitOnChange' => true,
    ],
    'exclude' => true
];
$GLOBALS['TL_DCA']['tl_settings']['fields']['catalogMapPrivacyText'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['catalogMapPrivacyText'],
    'inputType' => 'textarea',
    'eval' => [
        'tl_class' => 'clr',
        'allowHtml' => true
    ],
    'exclude' => true
];
$GLOBALS['TL_DCA']['tl_settings']['fields']['catalogMapPrivacyButtonText'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['catalogMapPrivacyButtonText'],
    'inputType' => 'text',
    'eval' => [
        'tl_class' => 'w50',
        'allowHtml' => true
    ],
    'exclude' => true
];