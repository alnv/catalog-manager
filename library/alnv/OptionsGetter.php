<?php

namespace CatalogManager;

class OptionsGetter extends CatalogController {


    protected $strModuleID;
    protected $arrCache = [];
    protected $arrField = [];
    protected $arrCatalog = [];
    protected $arrActiveEntity = [];
    protected $arrCatalogFields = [];


    public function __construct( $arrField, $strModuleID = '' ) {

        parent::__construct();

        $this->arrField = $arrField;
        $this->strModuleID = $strModuleID;

        $this->import( 'CatalogInput' );
        $this->import( 'SQLQueryHelper' );
        $this->import( 'SQLQueryBuilder' );
        $this->import( 'CatalogDCAExtractor' );
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

            case 'useActiveDbOptions':

                return $this->getActiveDbOptions();

                break;
        }

        return [];
    }


    protected function setValueToOption( $arrOptions, $strValue, $strLabel = '' ) {

        if ( $strValue && !in_array( $strValue, $arrOptions ) ) {

            $arrOptions[ $strValue ] = $this->I18nCatalogTranslator->getOptionLabel( $strValue, $strLabel );
        }

        return $arrOptions;
    }


    protected function parseCatalogValues( $varValue, $strFieldname, $arrCatalog ) {

        $arrField = $this->arrCatalogFields[ $strFieldname ];

        switch ( $arrField['type'] ) {

            case 'select':
                
                return Select::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'checkbox':

                return Checkbox::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'radio':

                return Radio::parseValue( $varValue, $arrField, $arrCatalog );

                break;
        }

        return $varValue;
    }


    protected function getDbOptions() {

        $strOrderBy = '';
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
        $arrQueries = Toolkit::parseQueries( $arrQueries, function( $arrQuery ) {

            $blnValidValue = true;
            $blnIgnoreEmptyValues = $this->arrField['dbIgnoreEmptyValues'] ? true : false;
            $arrQuery['value'] = $this->getParseQueryValue( $arrQuery['value'], $arrQuery['operator'], $blnValidValue );
            $arrQuery['allowEmptyValues'] = $blnIgnoreEmptyValues ? false : true;

            if ( !$blnValidValue ) return null;

            return $arrQuery;
        });

        $arrSQLQuery['where'] = $arrQueries;
        $this->CatalogDCAExtractor->extract( $this->arrField['dbTable'] );
        $strWhereStatement = $this->SQLQueryBuilder->getWhereQuery( $arrSQLQuery );

        if ( $this->CatalogDCAExtractor->hasOrderByStatement() ) {

            $strOrderBy = ' ORDER BY ' . $this->CatalogDCAExtractor->getOrderByStatement();
        }

        $strQuery = sprintf( 'SELECT * FROM %s %s%s', $this->arrField['dbTable'], $strWhereStatement, $strOrderBy );
        $objDbOptions = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare($strQuery)->execute( $this->SQLQueryBuilder->getValues() );

        while ( $objDbOptions->next() ) {

            $arrOptions[ $objDbOptions->{$this->arrField['dbTableKey']} ] = $this->I18nCatalogTranslator->getOptionLabel( $objDbOptions->{$this->arrField['dbTableKey']}, $objDbOptions->{$this->arrField['dbTableValue']} );
        }

        return $arrOptions;
    }

    
    protected function getActiveDbOptions() {

        $strOrderBy = '';
        $arrOptions = [];
        $strDbColumn = $this->arrField['dbColumn'];

        if ( !$this->arrField['dbTable'] || !$strDbColumn ) {

            return $arrOptions;
        }

        if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists( $this->arrField['dbTable'] ) ) {

            return $arrOptions;
        }

        if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( $strDbColumn, $this->arrField['dbTable'] ) ) {

            return $arrOptions;
        }

        $arrSQLQuery = [

            'table' => $this->arrField['dbTable'],
            'where' => []
        ];

        $this->getActiveEntityValues();
        $arrQueries = Toolkit::deserialize( $this->arrField['dbTaxonomy'] )['query'];
        $arrQueries = Toolkit::parseQueries( $arrQueries, function( $arrQuery ) {

            $blnIgnoreEmptyValues = $this->arrField['dbIgnoreEmptyValues'] ? true : false;
            $arrQuery['value'] = $this->getParseQueryValue( $arrQuery['value'], $arrQuery['operator'] );
            $arrQuery['allowEmptyValues'] = $blnIgnoreEmptyValues ? false : true;

            return $arrQuery;
        });

        $arrSQLQuery['where'] = $arrQueries;
        $this->CatalogDCAExtractor->extract( $this->arrField['dbTable'] );
        $strWhereStatement = $this->SQLQueryBuilder->getWhereQuery( $arrSQLQuery );

        if ( $this->CatalogDCAExtractor->hasOrderByStatement() ) {

            $strOrderBy = ' ORDER BY ' . $this->CatalogDCAExtractor->getOrderByStatement();
        }

        $strQuery = sprintf( 'SELECT * FROM %s %s%s', $this->arrField['dbTable'], $strWhereStatement, $strOrderBy );
        $objEntities = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare($strQuery)->execute( $this->SQLQueryBuilder->getValues() );

        if ( !$objEntities->numRows ) return $arrOptions;

        $this->arrCatalog = $this->SQLQueryHelper->getCatalogByTablename( $this->arrField['dbTable'] );
        $this->arrCatalogFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogTablename( $this->arrField['dbTable'] );

        while ( $objEntities->next() ) {

            $strOriginValue = $objEntities->{$strDbColumn};
            $varValue = $this->parseCatalogValues( $strOriginValue, $strDbColumn, [] );

            if ( is_array( $varValue ) ) {

                $arrLabels = array_values( $varValue );
                $arrOriginValues = array_keys( $varValue );

                if ( !empty( $arrLabels ) && is_array( $arrLabels ) ) {

                    foreach ( $arrLabels as $intPosition => $strLabel ) {

                        $arrOptions = $this->setValueToOption( $arrOptions, $arrOriginValues[ $intPosition ], $strLabel );
                    }
                }
            }

            else {

                $arrOptions = $this->setValueToOption( $arrOptions, $strOriginValue, $varValue );
            }
        }

        return $arrOptions;
    }


    protected function getParseQueryValue( $strValue = '', $strOperator = '', &$blnValidValue = true ) {

        if ( !empty( $strValue ) && is_string( $strValue ) ) {

            $strInsertTagValue = \Controller::replaceInsertTags( $strValue );

            if ( !Toolkit::isEmpty( $strInsertTagValue ) ) {

                $strValue = $strInsertTagValue;
            }
        }

        if ( !empty( $strValue ) && is_string( $strValue ) && strpos( $strValue, '{{' ) !== false ) {

            $strFieldnameValue = '';
            $arrTags = preg_split( '/{{(([^{}]*|(?R))*)}}/', $strValue, -1, PREG_SPLIT_DELIM_CAPTURE );
            $strTag = implode( '', $arrTags );

            if ( $strTag ) {

                $strFieldnameValue = $this->arrActiveEntity[ $strTag ];
            }

            if ( TL_MODE == 'FE' && ( is_null( $strFieldnameValue ) || $strFieldnameValue === '' ) ) {

                $strFieldnameValue = $this->CatalogInput->getValue( $strTag );
            }

            if ( Toolkit::isEmpty( $strFieldnameValue ) ) {

                $blnValidValue = $this->isValidValue( $strFieldnameValue );
                $strFieldnameValue = '';
            }

            $strValue = $strFieldnameValue;

        }

        if ( $strOperator == 'contain' && is_string( $strValue ) ) {

            $strValue = explode( ',', $strValue );
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

                $arrQuery = [

                    'table' => $strTable,

                    'pagination' => [

                        'limit' => 1
                    ],

                    'where' => [

                        [
                            'field' => 'id',
                            'value' => $strID,
                            'operator' => 'equal'
                        ]
                    ],

                    'joins' => []
                ];

                $objCatalog = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( 'SELECT * FROM tl_catalog WHERE tablename = ? LIMIT 1' )->execute( $strTable );

                if ( $objCatalog->numRows ) {

                    if ( $objCatalog->pTable && $this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( 'pid', $strTable ) ) {

                        $arrQuery['joins'][] = [

                            'field' => 'pid',
                            'onField' => 'id',
                            'multiple' => false,
                            'table' => $strTable,
                            'onTable' => $objCatalog->pTable
                        ];
                    }
                }

                $this->arrActiveEntity = $this->SQLQueryBuilder->execute( $arrQuery )->row();

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

                $strID = $this->strModuleID ? \Input::get( 'id'. $this->strModuleID ) : \Input::get('id');

                $arrQuery = [

                    'table' => $objCatalog->tablename,

                    'pagination' => [

                        'limit' => 1
                    ],

                    'where' => [

                        [
                            'field' => 'id',
                            'value' => $strID,
                            'operator' => 'equal'
                        ]
                    ],

                    'joins' => []
                ];

                if ( $objCatalog->pTable && $this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( 'pid', $objCatalog->tablename )) {

                    $arrQuery['joins'][] = [

                        'field' => 'pid',
                        'onField' => 'id',
                        'multiple' => false,
                        'onTable' => $objCatalog->pTable,
                        'table' => $objCatalog->tablename
                    ];
                }

                $this->arrActiveEntity = $this->SQLQueryBuilder->execute( $arrQuery )->row();
                
                break;

        }

        if ( !is_array( $this->arrActiveEntity ) ) {

            $this->arrActiveEntity = [];
        }
    }

    protected function isValidValue( $strValue ) {

        if ( !Toolkit::isEmpty( $strValue ) ) return true;

        switch ( TL_MODE ) {

            case 'BE':

                $strID = \Input::get('id');
                $strTable = \Input::get( 'table' ) ? \Input::get( 'table' ) : \Input::get('do');

                if ( !$strID || !$strTable ) return false;

                break;

            case 'FE':

                $strID = $this->strModuleID ? \Input::get( 'id'. $this->strModuleID ) : \Input::get('id');

                if ( !$strID ) return false;

                break;
        }

        return true;
    }
}