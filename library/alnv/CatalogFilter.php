<?php

namespace CatalogManager;

class CatalogFilter extends CatalogController {


    public $strTable;
    public $arrFields = [];
    public $arrOptions = [];
    public $arrActiveFields = [];

    private $arrForbiddenFilterTypes = [

        'map',
        'message',
        'fieldsetStop',
        'fieldsetStart'
    ];


    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
    }


    public function initialize() {

        $this->setOptions();
        $this->getFilterFields();
        $this->setActiveFields();
    }


    protected function getFilterFields() {

        if ( !$this->strTable ) return null;

        $objCatalogFields = $this->Database->prepare('SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT id FROM tl_catalog WHERE tablename = ? )')->execute( $this->strTable );

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->fieldname ) continue;

            if ( in_array( $objCatalogFields->type, $this->arrForbiddenFilterTypes ) ) continue;

            $this->arrFields[ $objCatalogFields->id ] = $objCatalogFields->row();
        }

        return $this->arrFields;
    }


    protected function setOptions() {

        if ( !empty( $this->arrOptions ) && is_array( $this->arrOptions ) ) {

            foreach ( $this->arrOptions as $strKey => $varValue ) {

                $this->{$strKey} = $varValue;
            }
        }
    }


    protected function setActiveFields() {

        $this->catalogActiveFilterFields = Toolkit::deserialize( $this->catalogActiveFilterFields );

        if ( !empty( $this->catalogActiveFilterFields ) && is_array( $this->catalogActiveFilterFields ) ) {

            foreach ( $this->catalogActiveFilterFields as $strFieldID ) {

                if ( !$this->arrFields[ $strFieldID ] ) continue;

                $this->arrActiveFields[ $strFieldID ] = $this->arrFields[ $strFieldID ];
            }
        }
    }
}