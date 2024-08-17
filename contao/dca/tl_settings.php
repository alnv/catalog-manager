<?php

use Alnv\CatalogManagerBundle\Classes\tl_settings;

$GLOBALS['TL_DCA']['tl_settings']['config']['onload_callback'] = [
    [tl_settings::class, 'changeGlobals']
];

$GLOBALS['TL_DCA']['tl_settings']['palettes']['__selector__'][] = 'catalogMapProtected';
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{catalog_manager_settings},catalogGoogleMapsClientKey,catalogNavigationAreas;{catalog_manager_dsgvo_legend},catalogMapProtected';
$GLOBALS['TL_DCA']['tl_settings']['subpalettes']['catalogMapProtected'] = 'catalogMapPrivacyText,catalogMapPrivacyButtonText';

$GLOBALS['TL_DCA']['tl_settings']['fields']['catalogGoogleMapsClientKey'] = [
    'inputType' => 'text',
    'eval' => [
        'tl_class' => 'w50'
    ]
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['catalogNavigationAreas'] = [
    'inputType' => 'keyValueWizard',
    'eval' => [
        'tl_class' => 'clr'
    ],
    'save_callback' => [[tl_settings::class, 'parseNavigationName']]
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['catalogMapProtected'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr',
        'submitOnChange' => true,
    ],
    'exclude' => true
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['catalogMapPrivacyText'] = [
    'inputType' => 'textarea',
    'eval' => [
        'tl_class' => 'clr',
        'allowHtml' => true
    ],
    'exclude' => true
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['catalogMapPrivacyButtonText'] = [
    'inputType' => 'text',
    'eval' => [
        'tl_class' => 'w50',
        'allowHtml' => true
    ],
    'exclude' => true
];