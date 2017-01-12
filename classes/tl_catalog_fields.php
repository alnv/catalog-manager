<?php

namespace CatalogManager;

class tl_catalog_fields extends \Backend {

    public function getFieldTypes() {

        return [

            'text',
            'date',
            'radio',
            'hidden',
            'number',
            'select',
            'upload',
            'message',
            'checkbox',
            'textarea',
            'fieldsetStart',
            'fieldsetStop'
        ];
    }

    public function getRGXPTypes( \DataContainer $dc ) {

        if ( $dc->activeRecord->type && $dc->activeRecord->type == 'number') {

            return [ 'digit', 'natural', 'prcnt' ];
        }

        if ( $dc->activeRecord->type && $dc->activeRecord->type == 'date') {

            return [ 'date', 'time', 'datim' ];
        }

        return [

            'url',
            'time',
            'date',
            'alias',
            'alnum',
            'alpha',
            'datim',
            'digit',
            'email',
            'extnd',
            'phone',
            'prcnt',
            'locale',
            'emails',
            'natural',
            'friendly',
            'language',
            'folderalias',
        ];
    }

    public function getRichTextEditor() {

        return [

            'tinyMCE',
            'tinyFlash'
        ];
    }

    public function getSQLStatements() {

        return DCABuilder::$arrSQLStatements;
    }

    public function getCatalogFieldList( $arrRow ) {

        return $arrRow['title'];
    }
}