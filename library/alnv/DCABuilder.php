<?php

namespace CatalogManager;

class DCABuilder {

    public static $arrForbiddenInputTypesMap = [

        'message',
        'fieldsetStart',
        'fieldsetStop'
    ];

    public static $arrInputTypeMap = [

        'text' => 'text',
        'date' => 'text',
        'number' => 'text',
        'hidden' => 'text',
        'radio' => 'radio',
        'select' => 'select',
        'upload' => 'fileTree',
        'textarea' => 'textarea',
        'checkbox' => 'checkbox'
    ];

    public static $arrSQLStatements = [

        'c256' => "varchar(256) NOT NULL default ''",
        'c1' => "char(1) NOT NULL default ''",
        'c16' => "varchar(16) NOT NULL default ''",
        'c32' => "varchar(32) NOT NULL default ''",
        'c64' => "varchar(64) NOT NULL default ''",
        'c128' => "varchar(128) NOT NULL default ''",
        'c512' => "varchar(512) NOT NULL default ''",
        'c1024' => "varchar(1024) NOT NULL default ''",
        'c2048' => "varchar(2048) NOT NULL default ''",
        'i5' => "smallint(5) unsigned NOT NULL default '0'",
        'i10' => "int(10) unsigned NOT NULL default '0'",
        'text' => "text NULL",
        'blob' => "blob NULL",
    ];

    public static function createConfigDCA( $arrCatalog, $arrFields ) {

        $arrReturn = [

            'dataContainer' => 'Table',

            'sql' => [

                'keys' => [

                    'id' => 'primary'
                ]
            ]
        ];

        if ( static::usePTable( $arrCatalog ) ) {

            $arrReturn['ptable'] = $arrCatalog['pTable'];
        }

        if ( $arrCatalog['cTables'] ) {

            $arrReturn['ctable'] = $arrCatalog['cTables'];
        }

        foreach ( $arrFields as $arrField ) {

            if ( !$arrField['useIndex'] ) {

                continue;
            }

            $arrReturn['sql']['keys'][ $arrField['fieldname'] ] = $arrField['useIndex'];
        }

        return $arrReturn;
    }

    public static function createDCAOperations( $arrCatalog ) {

        $arrReturn = [

            'edit' => [

                // 'label' => [ '…', '…' ],
                'href' => 'act=edit',
                'icon' => 'header.gif'
            ],

            'delete' => [

                // 'label' => [ '…', '…' ],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],

            'show' => [

                // 'label' => [ '…', '…' ],
                'href' => 'act=show',
                'icon' => 'show.gif'
            ]
        ];

        foreach ( $arrCatalog['cTables'] as $arrCTable ) {

            $arrChildTable = [];
            $strOperationName = sprintf( 'go_to_%s', $arrCTable );

            $arrChildTable[ $strOperationName ] = [

                //'label' => [ '…', '…' ],
                'href' => sprintf( 'table=%s', $arrCTable ),
                'icon' => 'edit.gif'
            ];

            array_insert( $arrReturn, 1, $arrChildTable );
        }

        return $arrReturn;
    }

    public static function createDCAGlobalOperations( $arrCatalog ) {

        return [

            'all' => array
            (
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ];
    }

    public static function createDCASorting( $arrCatalog ) {

        $arrFields = $arrCatalog['fields'];
        $headerFields = $arrCatalog['headerFields'];
        $strPanelLayout = implode( ',', $arrCatalog['panelLayout'] );

        if ( $arrCatalog['mode'] == '4' && empty( $arrCatalog['fields'] ) ) {

            $arrFields = [ 'sorting' ];
        }

        if ( empty( $arrCatalog['fields'] ) ) {

            $arrFields = [ 'title' ];
        }

        if ( empty( $headerFields ) ) {

            $headerFields = [ 'id', 'title', 'alias' ];
        }

        if ( strpos( $strPanelLayout, 'filter' ) !== false ) {

            $strPanelLayout = preg_replace( '/,/' , ';', $strPanelLayout, 1);
        }

        $arrReturn = [

            'fields' => $arrFields,
            'mode' => $arrCatalog['mode'],
            'flag' => $arrCatalog['flag'],
            'headerFields' => $headerFields,
            'panelLayout' => $strPanelLayout,
            'child_record_callback' => [ 'DCABuilder', 'createRowView' ],
        ];

        if ( $arrCatalog['mode'] === '5' ) {

            unset( $arrReturn['flag'] );
            unset( $arrReturn['headerFields'] );
        }

        return $arrReturn;
    }

    public function createRowView( $arrRow ) {

        // @todo hook
        return sprintf( '%s', $arrRow['title'] );
    }

    public static function createLabelDCA( $arrCatalog ) {

        $arrReturn = [

            'showColumns' => $arrCatalog['showColumns'] ? true : false,
            'fields' => empty( $arrCatalog['fields'] ) ? [ 'title' ] : $arrCatalog['fields'],
        ];

        if ( $arrCatalog['format'] ) {

            $arrReturn['format'] = $arrCatalog['format'];
        }

        return $arrReturn;
    }

    public static function createDCAPalettes( $arrFields ) {

        $strPalette = '';
        $strLegendPointer = 'general_legend';
        $arrDCAPalette = [ 'general_legend' => [ 'title', 'alias' ] ];

       foreach ( $arrFields as $arrField ) {

            if ( !$arrField['type'] ) {

                continue;
            }

            if ( $arrField['title'] && $arrField['type'] == 'fieldsetStart' ) {

                $strLegendPointer = $arrField['title'];
            }

            if ( !$arrField['fieldname'] || in_array( $arrField['type'], static::$arrForbiddenInputTypesMap ) ) {

                continue;
            }

            $arrDCAPalette[ $strLegendPointer ][] = $arrField['fieldname'];
        }

        $arrLegends = array_keys( $arrDCAPalette );

        foreach ( $arrLegends as $strLegend ) {

            $strPalette .= sprintf( '{%s},%s;', $strLegend, implode( ',', $arrDCAPalette[ $strLegend ] ) );
        }

        return [ 'default' => $strPalette ];
    }

    public static function getDefaultDCAFields( $arrCatalog ) {

        $arrReturn = [

            'id' => [

                'sql' => "int(10) unsigned NOT NULL auto_increment"
            ],

            'tstamp' => [

                'sql' => "int(10) unsigned NOT NULL default '0'"
            ],

            'title' => [

                // 'label' => ['…', '…'],
                'inputType' => 'text',

                'eval' => [

                    'maxlength' => 128,
                    'tl_class' => 'w50',
                ],

                'exclude' => true,
                'sql' => "varchar(128) NOT NULL default ''"
            ],

            'alias' => [

                // 'label' => ['…', '…'],
                'inputType' => 'text',

                'eval' => [

                    'maxlength' => 128,
                    'tl_class' => 'w50',
                ],

                'exclude' => true,
                'sql' => "varchar(128) NOT NULL default ''"
            ]
        ];

        if ( $arrCatalog['mode'] == '4' ) {

            $arrReturn['sorting'] = [

                'sql' => "int(10) unsigned NOT NULL default '0'"
            ];
        }

        if ( $arrCatalog['pTable'] ) {

            $arrReturn['pid'] = [

                'sql' => "int(10) unsigned NOT NULL default '0'",
            ];

            if ( !static::usePTable( $arrCatalog ) ) {

                $arrReturn['pid']['foreignKey'] = sprintf( '%s.id', $arrCatalog['pTable'] );
                $arrReturn['pid']['relation'] = [

                    'type' => 'belongsTo',
                    'load' => 'eager'
                ];
            }
        }

        return $arrReturn;
    }

    public static function createDCAField( $arrField ) {

        if ( !$arrField ) {

            return null;
        }

        if ( !$arrField['type'] ) {

            return null;
        }

        if ( in_array( $arrField['type'], static::$arrForbiddenInputTypesMap ) ) {

            return null;
        }

        $arrDCAField = [

            'label' => static::setFieldLabel( $arrField ),
            'inputType' => static::setInputType( $arrField ),

            'eval' => [

                'mandatory' => static::setMandatory( $arrField )
            ],

            'exclude' => true,
            'sql' => static::$arrSQLStatements[ $arrField['statement'] ]
        ];

        if ( $arrField['value'] ) {

            $arrDCAField['default'] = $arrField['value'];
        }

        switch ( $arrField['type'] ) {

            case 'text':

                $arrDCAField = Text::generate( $arrDCAField, $arrField );

                break;

            case 'date':

                $arrDCAField = DateInput::generate( $arrDCAField, $arrField );

                break;

            case 'hidden':

                $arrDCAField = Hidden::generate( $arrDCAField, $arrField );

                break;

            case 'number':

                $arrDCAField = Number::generate( $arrDCAField, $arrField );

                break;

            case 'textarea':

                $arrDCAField = Textarea::generate( $arrDCAField, $arrField );

                break;

            case 'select':

                $arrDCAField = Select::generate( $arrDCAField, $arrField );

                break;

            case 'radio':

                $arrDCAField = Radio::generate( $arrDCAField, $arrField );

                break;

            case 'checkbox':

                $arrDCAField = Checkbox::generate( $arrDCAField, $arrField );

                break;

            case 'upload':

                $arrDCAField = Upload::generate( $arrDCAField, $arrField );

                break;
        }

        return $arrDCAField;
    }

    public static function setMandatory( $arrField ) {

        return $arrField['mandatory'] ? true : false;
    }

    public static function setFieldLabel( $arrField ) {

        $strTitle = $arrField['label'] ? $arrField['label'] : '';

        if ( !$strTitle ) {

            $strTitle = $arrField['title'];
        }

        $strDescription = $arrField['description'] ? $arrField['description'] : '';

        return [ $strTitle, $strDescription ];
    }

    public static function setInputType( $arrField ) {

        return static::$arrInputTypeMap[ $arrField['type'] ] ? static::$arrInputTypeMap[ $arrField['type'] ] : 'text';
    }

    private static function usePTable( $arrCatalog ) {

        if ( !$arrCatalog['pTable'] ) {

            return false;
        }

        if ( $arrCatalog['isBackendModule'] ) {

            return false;
        }

        if ( in_array( $arrCatalog['mode'], [ '4', '5' ] ) ) {

            return false;
        }

        return true;
    }
}