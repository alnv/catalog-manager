<?php

namespace CatalogManager;

class OptionsGetter extends CatalogController {


    protected $arrField = [];
    protected $arrActiveEntity = [];


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


    protected function getDbOptions() {

        $arrOptions = [];

        if ( !$this->arrField['dbTable'] || !$this->arrField['dbTableKey'] || !$this->arrField['dbTableValue'] ) {

            return $arrOptions;
        }

        if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists( $this->arrField['dbTable'] ) ) {

            return $arrOptions;
        }

        if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( $this->arrField['dbTableKey'], $this->arrField['dbTable'] ) || !$this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( $this->arrField['dbTableValue'], $this->arrField['dbTable'] ) ) {

            return $arrOptions;
        }

        $arrSQLQuery = [

            'table' => $this->arrField['dbTable'],
            'where' => []
        ];

        $this->getActiveEntityValues();
        $arrQueries = Toolkit::deserialize( $this->arrField['dbTaxonomy'] )['query'];
        $arrQueries = Toolkit::parseWhereQueryArray( $arrQueries, function( $arrQuery ) {

            $arrQuery['value'] = $this->getParseQueryValue( $arrQuery['value'], $arrQuery['operator'] );
            
            if ( is_null( $arrQuery['value'] ) ) {

                return null;
            }

            if ( empty( $arrQuery['value'] ) && is_array( $arrQuery['value'] ) ) {

                return null;
            }

            return $arrQuery;
        });

        $arrSQLQuery['where'] = $arrQueries;
        $objDbOptions = $this->SQLQueryBuilder->execute( $arrSQLQuery );

        while ( $objDbOptions->next() ) {

            $arrOptions[ $objDbOptions->{$this->arrField['dbTableKey']} ] = $this->I18nCatalogTranslator->getOptionLabel( $objDbOptions->{$this->arrField['dbTableKey']}, $objDbOptions->{$this->arrField['dbTableValue']} );
        }

        return $arrOptions;
    }


    protected function getParseQueryValue( $strValue = '', $strOperator = '' ) {

        if ( !empty( $strValue ) && is_string( $strValue ) && strpos( $strValue, '{{' ) !== false ) {

            $strFieldnameValue = '';
            $arrTags = preg_split( '/{{(([^{}]*|(?R))*)}}/', $strValue, -1, PREG_SPLIT_DELIM_CAPTURE );
            $strTag = implode( '', $arrTags );

            if ( $strTag ) {

                $strFieldnameValue = $this->arrActiveEntity[ $strTag ];
            }

            if ( TL_MODE == 'FE' && ( is_null( $strFieldnameValue ) || $strFieldnameValue === '' ) ) {

                $strFieldnameValue = \Input::get( $strTag ) ? \Input::get( $strTag ) : '';
            }

            $strValue = $strFieldnameValue ? $strFieldnameValue : '';
        }

        if ( $strOperator == 'contain' && is_string( $strValue )) {

            $strValue = explode( ',', $strValue );
        }

        if ( $strValue && is_string( $strValue ) && $this->arrField['multiple'] ) {

            // @todo
        }

        return Toolkit::prepareValueForQuery( $strValue );
    }


    protected function getKeyValueOptions() {

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


    protected function setForeignKey() {

        if ( !$this->arrField['dbTable'] || !$this->arrField['dbTableKey'] ) {

            return '';
        }

        return $this->arrField['dbTable'] . '.' . $this->arrField['dbTableKey'];
    }


    protected function getActiveEntityValues() {

        switch ( TL_MODE ) {

            case 'BE':

                $strID = \Input::get('id');
                $strTable = \Input::get( 'table' ) ? \Input::get( 'table' ) : \Input::get('do');

                if ( !$strID || !$strTable ) {

                    return null;
                }

                if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists( $strTable ) ) {

                    return null;
                }

                $this->arrActiveEntity = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `id` = ?', $strTable ) )->limit(1)->execute( $strID )->row();

                return null;

                break;

            case 'FE':

                if ( !$this->arrField['pid'] ) {

                    return null;
                }

                $objCatalog = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( 'SELECT * FROM tl_catalog WHERE id = ?' )->limit(1)->execute( $this->arrField['pid'] );

                if ( !$objCatalog->tablename || !$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists( $objCatalog->tablename ) ) {

                    return null;
                }

                $this->arrActiveEntity = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `id` = ?', $objCatalog->tablename ) )->limit(1)->execute( \Input::get('id') )->row();

                break;

        }

        if ( !is_array( $this->arrActiveEntity ) ) {

            $this->arrActiveEntity = [];
        }
    }
}