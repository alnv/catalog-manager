<?php

namespace CatalogManager;

class CatalogFilter extends CatalogController {


    public $strTable;
    public $arrFields = [];
    public $arrCatalog = [];
    public $arrOptions = [];
    public $arrDependencies = [];
    public $arrActiveFields = [];

    private $arrForbiddenFilterTypes = [

        'map',
        'upload',
        'message',
        'fieldsetStop',
        'fieldsetStart'
    ];


    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
        $this->import( 'DCABuilderHelper' );
    }


    public function initialize() {

        $this->setOptions();
        $this->setCatalog();
        $this->getFilterFields();
        $this->setActiveFields();
    }


    public function generateForm() {

        $intIndex = 0;
        $strFields = '';

        if ( !empty( $this->arrActiveFields ) && is_array( $this->arrActiveFields ) ) {

            $arrFieldTemplates = Toolkit::deserialize( $this->catalogFilterFieldTemplates );
            $arrFieldDependencies = Toolkit::deserialize( $this->catalogFilterFieldDependencies );
            $arrDCFields = $this->DCABuilderHelper->convertCatalogFields2DCA( $this->arrActiveFields, [], $this->arrCatalog );

            foreach ( $arrDCFields as $arrField ) {

                $arrField = $this->convertWidgetToField( $arrField );
                $strClass = $this->fieldClassExist( $arrField['inputType'] );

                if ( $strClass == false ) return null;

                $objWidget = new $strClass( $strClass::getAttributesFromDca( $arrField, $arrField['_fieldname'], $arrField['default'], '', '' ) );

                $objWidget->id = 'id_' . $arrField['_fieldname'];
                $objWidget->value = \Input::get( $arrField['_fieldname'] ) ? \Input::get( $arrField['_fieldname'] ) : '';
                $objWidget->rowClass = 'row_' . $intIndex . ( ( $intIndex == 0 ) ? ' row_first' : '' ) . ( ( ( $intIndex % 2 ) == 0 ) ? ' even' : ' odd' );

                if ( $objWidget->value ) {

                    $this->arrDependencies[] = $arrField['_fieldname'];
                }

                if ( !empty( $arrFieldTemplates ) && is_array( $arrFieldTemplates ) ) {

                    $arrTemplate = $arrFieldTemplates[ $arrField['_fieldname'] ];

                    if ( $arrTemplate && $arrTemplate['value'] ) {

                        $objWidget->template = $arrTemplate['value'];
                    }
                }

                if ( !empty( $arrFieldDependencies ) && is_array( $arrFieldDependencies ) ) {

                    $arrDependencies = $arrFieldDependencies[ $arrField['_fieldname'] ];

                    if ( $arrDependencies && $arrDependencies['value'] && !in_array( $arrDependencies['value'], $this->arrDependencies )) {

                        continue;
                    }
                }
                
                $strFields .= $objWidget->parse();
                $intIndex++;
            }
        }

        return $strFields;
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


    protected function setCatalog() {

        $this->arrCatalog = $this->Database->prepare( 'SELECT * FROM tl_catalog WHERE tablename = ?' )->limit(1)->execute( $this->strTable )->row();
    }


    protected function convertWidgetToField( $arrField ) {

        if ( $arrField['inputType'] == 'checkboxWizard' ) {

            $arrField['inputType'] = 'checkbox';
        }

        $arrField['eval']['tableless'] = '1';
        $arrField['eval']['required'] = false;

        return $arrField;
    }


    protected function fieldClassExist( $strInputType ) {

        $strClass = $GLOBALS['TL_FFL'][ $strInputType ];

        if ( !class_exists( $strClass ) ) {

            return false;
        }

        return $strClass;
    }
}