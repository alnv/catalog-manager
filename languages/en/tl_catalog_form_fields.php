<?php

$GLOBALS['TL_LANG']['tl_catalog_form_fields']['new'] = [ 'Create Input field', 'Here you can create new input field.' ];

$GLOBALS['TL_LANG']['tl_catalog_form_fields']['date_legend'] = 'Date settings';
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['option_legend'] = 'Option settings';
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['general_legend'] = 'General settings';
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['template_legend'] = 'Template settings';
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['invisible_legend'] = 'Visibility settings';
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['field_type_legend'] = 'Input field settings';

$GLOBALS['TL_LANG']['tl_catalog_form_fields']['title'] = [ 'Title', 'Please enter a title.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['template'] = [ 'Template', 'Please select a template.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['type'] = [ 'Field type', 'Please select a field type.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['mandatory'] = [ 'Mandatory', 'Make this field mandatory.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['label'] = [ 'Field label', 'Please enter your field label.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['multiple'] = [ 'Multiple', 'Make the input field multiple.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['placeholder'] = [ 'Placeholder', 'Please enter your placeholder.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['description'] = [ 'Description', 'Please enter short description.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['invisible'] = [ 'Hide', 'Do not show this field in the filter form.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dependOnField'] = [ 'Related field', 'Please select a related field.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['defaultValue'] = [ 'Default-Value', 'Here you can enter default value.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['rangeLowLabel'] = [ 'Field label (low)', 'Please enter your field label.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['submitOnChange'] = [ 'Submit on change', 'Page will be reloaded by submit.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['includeBlankOption'] = [ 'Add blank option', 'Here you can add blank option.'];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['cssID'] = [ 'CSS ID/class', 'Here you can set an ID and one or more classes.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['rangeGreatLabel'] = [ 'Field label (great)', 'Please enter your field label.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['rgxp'] = [ 'Regular expression', 'Here you can select your regular expression.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['tabindex'] = [ 'Tab index', 'The position of the form field in the tabbing order.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['blankOptionLabel'] = [ 'Replace blank option', 'Here you can replace blank option.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['rangeLowType'] = [ 'Range (to)', 'Here you can adjust the accuracy of the circumference.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['rangeGreatType'] = [ 'Range (from)', 'Here you can adjust the accuracy of the circumference.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['name'] = [ 'Fieldname', 'Fieldname is a unique name for identifying the field, for example for the CTLG_ACTIVE insert tag. {{CTLG_ACTIVE :: *Fieldname}}' ];

$GLOBALS['TL_LANG']['tl_catalog_form_fields']['options'] = [ 'List', 'Please insert your select list.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['optionsType'] = [ 'Source', 'Here you can select a source from which the selection list should be generated.' ];

$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dbTable'] = [ 'Table', 'Please select a Table.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dbTableKey'] = [ 'Key column', 'Please select a key column.'  ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dbOrderBy'] = [ 'Sort order', 'Here you can order the entities.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dbTableValue'] = [ 'Value column', 'Please select a value column.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dbColumn'] = [ 'Column', 'Please select a column from chosen table.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dbTaxonomy'] = [ 'Taxonomies/Filter', 'Here you can filter the records.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dbDateFormat'] = [ 'Date format', 'Here you can specify the date format.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dbIgnoreEmptyValues'] = [ 'Ignore empty values', 'Empty values will be not filtered.' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['dbParseDate'] = [ 'Parse date', 'Here you can display date instead of timestamp in the selection list.' ];

$GLOBALS['TL_LANG']['tl_catalog_form_fields']['edit'] = [ 'Edit field', 'Edit field ID "%s".' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['copy'] = [ 'Copy field', 'Copy field ID "%s".' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['delete'] = [ 'Delete field', 'Delete field ID "%s".' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['toggle'] = [ 'Hide field', 'Disable or Enable field ID "%s".' ];
$GLOBALS['TL_LANG']['tl_catalog_form_fields']['show'] = [ 'Show field', 'Show the details of field ID "%s".' ];

$GLOBALS['TL_LANG']['tl_catalog_form_fields']['reference']['dbDateFormat'] = [

    'monthBegin' => 'Month and year',
    'yearBegin' => 'Year'
];

$GLOBALS['TL_LANG']['tl_catalog_form_fields']['reference']['type'] = [

    'range' => 'Range',
    'text' => 'Text field',
    'radio' => 'Radio menu',
    'select' => 'select menu',
    'checkbox' => 'Checkbox menu',
    'hidden' => 'Hidden field'
];

$GLOBALS['TL_LANG']['tl_catalog_form_fields']['reference']['optionsType'] = [

    'useOptions' => 'Options',
    'useDbOptions' => 'Database',
    'useActiveDbOptions' => 'Database (assigned)',
];

$GLOBALS['TL_LANG']['tl_catalog_form_fields']['reference']['rangeLowType'] = [

    'lt' => 'Lower',
    'lte' => 'Lower equal',
];

$GLOBALS['TL_LANG']['tl_catalog_form_fields']['reference']['rangeGreatType'] = [

    'gt' => 'Greater',
    'gte' => 'Greater equal',
];

$GLOBALS['TL_LANG']['tl_catalog_form_fields']['reference']['rgxp'] = [

    'url' => 'Valid URL.',
    'time' => 'Valid time.',
    'date' => 'Valid date.',
    'alias' => 'Valid alias.',
    'alnum' => 'Alphanumeric characters.',
    'alpha' => 'Alphabetic characters.',
    'datim' => 'Valid date and time.' ,
    'digit' => 'Numeric Values.',
    'email' => 'Valid E Mail Address.' ,
    'extnd' => 'Disallows "#&()/<=>"',
    'phone' => 'Valid phone number.',
    'prcnt' => 'Valid percent values.',
    'locale' => 'Valid locale (de-CH).',
    'emails' => 'Valid list of E Mails.',
    'natural' => 'Allows non-negative numbers.',
    'friendly' => 'Valid E-Mail Address "friendly name format".',
    'language' => 'Valid language code.',
    'folderalias' => 'Valid folder URL alias.'
];