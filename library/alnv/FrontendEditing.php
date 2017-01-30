<?php

namespace CatalogManager;

class FrontendEditing extends CatalogController {

    private $strTable = '';
    private $arrCatalog = [];
    private $arrFormFields = [];
    private $strFormTemplate = '';

    public function __construct() {

        parent::__construct();

        $this->import( 'SQLQueryBuilder' );
        $this->import( 'SQLHelperQueries' );
        $this->import( 'DCABuilderHelper' );
    }

    public function getCatalogByTablename( $strTablename ) {

        return $this->SQLHelperQueries->getCatalogByTablename( $strTablename );
    }

    public function getCatalogFieldsByCatalogID( $strID ) {

        return $this->SQLHelperQueries->getCatalogFieldsByCatalogID( $strID, [ 'FrontendEditing', 'createDCField' ] );
    }

    public function getCatalogFormByTablename( $strTablename ) {

        $this->strTable = $strTablename;

        if ( !$this->SQLQueryBuilder->tableExist( $this->strTable ) ) return '';

        $arrPredefinedDCFields = $this->DCABuilderHelper->getPredefinedDCFields();

        $this->arrCatalog = $this->getCatalogByTablename( $this->strTable );
        $this->arrFormFields = $this->getCatalogFieldsByCatalogID( $this->arrCatalog['id'] );

        $this->arrFormFields[] = $arrPredefinedDCFields['invisible'];

        unset( $arrPredefinedDCFields['invisible'] );

        array_insert( $this->arrFormFields, 0, $arrPredefinedDCFields );

        if ( !empty( $this->arrFormFields ) && is_array( $this->arrFormFields ) ) {

            $intIndex = 0;

            foreach ( $this->arrFormFields as $arrField ) {

                $this->createForm( $arrField, $intIndex );

                $intIndex++;
            }
        }

        return $this->strFormTemplate;
    }

    public function createForm( $arrField, $intIndex ) {

        $strClass = $this->fieldClassExist( $arrField['inputType'] );

        if ( $strClass == false ) return null;

        $objWidget = new $strClass( $strClass::getAttributesFromDca( $arrField, $arrField['_fieldname'], $arrField['default'], '', '', $this ) );
        $objWidget->storeValues = true;
        $objWidget->rowClass = 'row_' . $intIndex . ( ( $intIndex == 0 ) ? ' row_first' : '' ) . ( ( ( $intIndex % 2 ) == 0 ) ? ' even' : ' odd' );

        if ( $objWidget instanceof \FormPassword ) {

            $objWidget->rowClassConfirm = 'row_' . ++$intIndex . ( ( ( $intIndex % 2 ) == 0 ) ? ' even' : ' odd' );
        }

        $this->strFormTemplate .= $objWidget->parse();
    }

    public function createDCField( $arrField, $intIndex, $intCount, $strFieldname ) {

        if ( empty( $arrField ) && !is_array( $arrField ) ) return null;

        if ( !$arrField['type'] ) return null;

        if ( in_array( $arrField['type'], $this->DCABuilderHelper->arrForbiddenInputTypes ) ) return null;

        $arrDCField = $this->DCABuilderHelper->convertCatalogField2DCA( $arrField );

        $arrDCField['_fieldname'] = $strFieldname;

        return $this->convertWidgetToField( $arrDCField );
    }

    protected function convertWidgetToField( $arrField ) {

        if ( $arrField['inputType'] == 'checkboxWizard' ) {

            $arrField['inputType'] = 'checkbox';
        }

        if ( $arrField['inputType'] == 'fileTree' ) {

            $arrField['inputType'] = 'upload';
        }

        $arrField['eval']['tableless'] = '';
        $arrField['eval']['required'] = $arrField['eval']['mandatory'];

        return $arrField;
    }

    protected function fieldClassExist( $strInputType ) {

        $strClass = $GLOBALS['TL_FFL'][$strInputType];

        if ( !class_exists( $strClass ) ) {

            return false;
        }

        return $strClass;
    }
}