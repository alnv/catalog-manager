<?php

$GLOBALS['TL_DCA']['tl_member_group']['palettes']['default'] = str_replace('redirect;', 'redirect;{catalog_manager_legend},isAdmin;', $GLOBALS['TL_DCA']['tl_member_group']['palettes']['default']);

$GLOBALS['TL_DCA']['tl_member_group']['fields']['isAdmin'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member_group']['isAdmin'],
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr',
    ],
    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];