<?php

namespace CatalogManager;

class tl_catalog extends \Backend {

    public function getModeTypes () {

        return [

            '0',
            '1',
            '2',
            '3',
            '4',
            '5'
        ];
    }

    public function getFlagTypes() {

        return [

            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            '11',
            '12'
        ];
    }

    public function getDataContainerFields() {

        return [];
    }

    public function getParentFields() {

        // get fields from CatalogManager
        return [];
    }

    public function getNavigationAreas() {

        return [];
    }

    public function getNavigationPlace() {

        return [];
    }
}