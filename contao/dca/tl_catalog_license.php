<?php

use Contao\DC_File;
use Contao\Environment;
use Alnv\ContaoCatalogManagerBundle\Security\Authorization;

$GLOBALS['TL_DCA']['tl_catalog_license'] = [
    'config' => [
        'dataContainer' => DC_File::class,
        'closed' => true
    ],
    'palettes' => [
        'default' => 'catalogLicence'
    ],
    'fields' => [
        'catalogLicence' => [
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'tl_class' => 'long clr'
            ],
            'save_callback' => [
                function ($strLicense) {
                    $objAuthorization = new Authorization();
                    if (!$objAuthorization->isValid($strLicense)) {
                        throw new Exception('Licence for domain ' . $objAuthorization->parseDomain(Environment::get('uri')) . ' is invalid! You can purchase a licence at this address: ' . $objAuthorization->getUrl());
                    }
                    return $strLicense;
                }
            ]
        ]
    ]
];