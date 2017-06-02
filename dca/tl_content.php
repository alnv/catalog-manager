<?php

$GLOBALS['TL_DCA']['tl_content']['palettes']['catalogFilterForm'] = '{type_legend},type,headline;{include_legend},catalogForm;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['catalogForm'] = [

    'label' => &$GLOBALS['TL_LANG']['tl_content']['catalogForm'],
    'inputType' => 'select',

    'eval' => [

        'chosen' => true,
        'mandatory' => true,
        'submitOnChange' => true,
        'blankOptionLabel' => '-',
        'tl_class' => 'w50 wizard',
        'includeBlankOption' => true,
    ],

    'wizard' => [

        [
            'CatalogManager\tl_content', 'editCatalogForm'
        ]
    ],

    'options_callback' => [ 'CatalogManager\tl_content', 'getCatalogForms' ],

    'exclude' => true,
    'sql' => "int(10) unsigned NOT NULL default '0'"
];

if ( \Input::get('do') ) {
    
    $arrCatalogs = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'];

    if ( !empty( $arrCatalogs ) && is_array( $arrCatalogs ) ) {

        foreach ( $arrCatalogs as $strTablename => $arrCatalog ) {

            if ( !$arrCatalog['tablename'] ) continue;

            if ( $arrCatalog['tablename'] == \Input::get( 'do' ) ) {

                $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = $arrCatalog['tablename'];
                
                break;
            }

            if ( $arrCatalog['pTable'] == \Input::get( 'do' ) ) {

                $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = $arrCatalog['tablename'];
                
                break;
            }
        }
    }
}