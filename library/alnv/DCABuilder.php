<?php

namespace CatalogManager;

class DCABuilder {

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

    public static $arrForbiddenInputTypesMap = [

        'message',
        'fieldsetStart',
        'fieldsetStop'
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
        'text' => "text NULL",
        'blob' => "blob NULL",
        'i5' => "smallint(5) unsigned NOT NULL default '0'",
        'i10' => "int(10) unsigned NOT NULL default '0'"
    ];

    public static function createConfigDCA() {

        return [];
    }

    public static function createDCASorting() {

        return [];
    }

    public static function createDCAOperations() {

        return [];
    }

    public static function createDCAPalettes() {

        return [];
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

                $arrDCAField = Date::generate( $arrDCAField, $arrField );

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

        // @todo i18n

        return [ $strTitle, $strDescription ];
    }

    public static function setInputType( $arrField ) {

        return static::$arrInputTypeMap[ $arrField['type'] ] ? static::$arrInputTypeMap[ $arrField['type'] ] : 'text';
    }
}