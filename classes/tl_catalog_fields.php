<?php

namespace CatalogMaker;

class tl_catalog_fields extends \Backend {

    public function getFieldTypes() {

        return [

            'text',
            'text',
            'date',
            'hidden',
            'number',
            'textarea',
            'select',
            'radio',
            'checkbox',
            'upload',
            'message',
            'fieldsetStart',
            'fieldsetStop'
        ];
    }

    public function getRGXPTypes() {

        return [

            'alias',
            'alnum',
            'alpha',
            'date',
            'datim',
            'digit',
            'email',
            'emails',
            'extnd',
            'folderalias',
            'friendly',
            'language',
            'locale',
            'natural',
            'phone',
            'prcnt',
            'url',
            'time'
        ];
    }

    public function getRichTextEditor() {

        return [

            'tinyMCE',
            'tinyFlash'
        ];
    }

    public function getSQLStatements() {

        return [

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
            'i10' => "int(10) unsigned NOT NULL default '0'"
        ];
    }

    public function getCatalogFieldList( $arrRow ) {

        return $arrRow['title'];
    }
}