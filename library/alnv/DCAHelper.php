<?php

namespace CatalogManager;

class DCAHelper {

    public static $arrForbiddenInputTypes = [

        'message',
        'fieldsetStart',
        'fieldsetStop'
    ];

    public static $arrInputTypes = [

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
    
    public static function setFieldLabel( $arrField ) {

        $strTitle = $arrField['label'] ? $arrField['label'] : '';

        if ( !$strTitle ) {

            $strTitle = $arrField['title'];
        }

        $strDescription = $arrField['description'] ? $arrField['description'] : '';

        return [ $strTitle, $strDescription ];
    }

    public static function setInputType( $arrField ) {
        
        return static::$arrInputTypes[ $arrField['type'] ] ? static::$arrInputTypes[ $arrField['type'] ] : 'text';
    }
}