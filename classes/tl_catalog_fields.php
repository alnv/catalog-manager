<?php

namespace OceanCatalog;

class tl_catalog_fields extends \Backend {

    public function getFieldTypes() {

        return [

            'text',
            'textarea',
            'select',
            'radio',
            'checkbox',
            'upload',
            'fieldset',
            'message'
        ];
    }

    public function getInputTypes() {

        return [

            'text',
            'hidden',
            'url',
            'number',
            'date'
        ];
    }
}