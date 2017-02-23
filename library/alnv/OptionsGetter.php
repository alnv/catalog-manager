<?php

namespace CatalogManager;

class OptionsGetter extends CatalogController {


    private $arrField = [];
    private $arrActiveEntity = [];


    public function __construct( $arrField ) {

        parent::__construct();

        $this->arrField = $arrField;

        $this->import( 'SQLQueryHelper' );
        $this->import( 'SQLQueryBuilder' );
        $this->import( 'I18nCatalogTranslator' );
    }


    public function isForeignKey() {

        if ( $this->arrField['optionsType'] && $this->arrField['optionsType'] == 'useForeignKey' ) {

            return true;
        }

        return false;
    }


    public function getForeignKey() {

        return $this->setForeignKey();
    }


    public function getOptions() {

        switch ( $this->arrField['optionsType'] ) {

            case 'useOptions':

                return $this->getKeyValueOptions();

                break;

            case 'useDbOptions':

                return $this->getDbOptions();

                break;
        }

        return [];
    }


    private function getDbOptions() {

        $arrOptions = [];

        if ( !$this->arrField['dbTable'] || !$this->arrField['dbTableKey'] || !$this->arrField['dbTableValue'] ) {

            return $arrOptions;
        }

        if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( $this->arrField['dbTableKey'], $this->arrField['dbTable'] ) || !$this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( $this->arrField['dbTableValue'], $this->arrField['dbTable'] ) ) {

            return $arrOptions;
        }

        $arrQuery = [

            'table' => $this->arrField['dbTable'],
            'where' => []
        ];

        $this->getActiveEntityValues();
        $arrQueries = Toolkit::deserialize( $this->arrField['dbTaxonomy'] )['query'];

        if ( !empty( $arrQueries ) && is_array( $arrQueries ) ) {

            $arrQuery['where'] = Toolkit::parseWhereQueryArray( $arrQueries, function( $arrQuery ) {

                $arrQuery['value'] = $this->getParseQueryValue( $arrQuery['field'], $arrQuery['value'], $arrQuery['operator'] );

                if ( !$arrQuery['value'] || empty( $arrQuery['value'] ) ) {

                    return null;
                }

                return $arrQuery;
            });
        }

        $objDbOptions = $this->SQLQueryBuilder->execute( $arrQuery );

        while ( $objDbOptions->next() ) {

            $arrOptions[ $objDbOptions->{$this->arrField['dbTableKey']} ] = $this->I18nCatalogTranslator->getOptionLabel( $objDbOptions->{$this->arrField['dbTableKey']}, $objDbOptions->{$this->arrField['dbTableValue']} );
        }

        return $arrOptions;
    }


    private function getParseQueryValue( $strFieldname, $strValue = '', $strOperator = '' ) {

        if ( !empty( $strValue ) && is_string( $strValue ) && strpos( $strValue, '{{' ) !== false ) {

            $strFieldnameValue = '';
            $arrTags = preg_split( '/{{(([^{}]*|(?R))*)}}/', $strValue, -1, PREG_SPLIT_DELIM_CAPTURE );
            $strTag = implode( '', $arrTags );

            if ( !empty( $arrTags ) && is_array( $arrTags ) ) {

                $strFieldnameValue = $this->arrActiveEntity[ $strTag ];
            }

            $strValue = $strFieldnameValue ? $strFieldnameValue : $strValue;
        }

        if ( $strOperator == 'contain' && is_string( $strValue )) {

            $strValue = explode( ',' , $strValue );
        }

        if ( $strValue && is_string( $strValue ) && $this->arrField['multiple'] ) {

            $strValue = Toolkit::deserialize( $strValue );
        }

        return Toolkit::prepareValueForQuery( $strValue );
    }


    private function getKeyValueOptions() {

        $arrOptions = [];

        if ( $this->arrField['options'] ) {

            $arrFieldOptions = deserialize( $this->arrField['options'] );

            if ( !empty( $arrFieldOptions ) && is_array( $arrFieldOptions ) ) {

                foreach ( $arrFieldOptions as $arrOption ) {

                    $arrOptions[ $arrOption['key'] ] = $this->I18nCatalogTranslator->getOptionLabel( $arrOption['key'], $arrOption['value'] );
                }
            }
        }

        return $arrOptions;
    }

    
    private function setForeignKey() {

        if ( !$this->arrField['dbTable'] || !$this->arrField['dbTableKey'] ) {

            return '';
        }

        return $this->arrField['dbTable'] . '.' . $this->arrField['dbTableKey'];
    }


    private function getActiveEntityValues() {

        switch ( TL_MODE ) {

            case 'BE':

                if ( !\Input::get('id') || !\Input::get('do') ) {

                    return null;
                }

                if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists( \Input::get('do') ) ) {

                    return null;
                }

                $this->arrActiveEntity = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( sprintf( 'SELECT * FROM %s WHERE id = ?', \Input::get('do') ) )->limit(1)->execute( \Input::get('id') )->row();

                return null;

                break;

            case 'FE':

                // @todo

                break;

        }

        if ( !is_array( $this->arrActiveEntity ) ) {

            $this->arrActiveEntity = [];
        }
    }
}