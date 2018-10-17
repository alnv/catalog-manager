<?php

namespace CatalogManager;


class ContentCatalogEntity extends \ContentElement {


    protected $arrFields = [];
    protected $strTemplate = 'ce_catalog_entity';


    public function generate() {

        if ( TL_MODE == 'BE' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . utf8_strtoupper( $GLOBALS['TL_LANG']['CTE']['catalogCatalogEntity'][0] ) . ' ###';

            return $objTemplate->parse();
        }

        $this->catalogEntityId = $this->catalogEntityId ?: '0';

        if ( !$this->catalogTablename || !$this->catalogEntityId ) {

            return '';
        }

        if ( !$this->Database->tableExists( $this->catalogTablename ) ) {

            return '';
        }

        if ( $this->catalogEntityTemplate ) {

            $this->strTemplate = $this->catalogEntityTemplate;
        }
        var_dump($this->strTemplate);
        return parent::generate();
    }


    protected function compile() {

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize( $this->catalogTablename );

        $arrCatalog = $objFieldBuilder->getCatalog();
        $arrFields = $objFieldBuilder->getCatalogFields();

        foreach ( $arrFields as $strFieldname => $strValue ) {

            if ( !is_numeric( $strFieldname ) ) {

                $this->arrFields[ $strFieldname ] = $strValue;
            }
        }

        $arrQuery = [

            'table' => $this->catalogTablename,
            'where' => [
                [
                    'field' => 'id',
                    'operator' => 'equal',
                    'value' => $this->catalogEntityId
                ]
            ],
            'joins' => [],
            'pagination' => [

                'limit' => 1,
                'offset' => 0
            ]
        ];

        if ( is_array( $arrCatalog['operations'] ) && in_array( 'invisible', $arrCatalog['operations'] ) ) {

            $dteTime = \Date::floorToMinute();

            $arrQuery['where'][] = [

                'field' => 'tstamp',
                'operator' => 'gt',
                'value' => 0
            ];

            $arrQuery['where'][] = [

                [
                    'value' => '',
                    'field' => 'start',
                    'operator' => 'equal'
                ],

                [
                    'field' => 'start',
                    'operator' => 'lte',
                    'value' => $dteTime
                ]
            ];

            $arrQuery['where'][] = [

                [
                    'value' => '',
                    'field' => 'stop',
                    'operator' => 'equal'
                ],

                [
                    'field' => 'stop',
                    'operator' => 'gt',
                    'value' => $dteTime
                ]
            ];

            $arrQuery['where'][] = [

                'field' => 'invisible',
                'operator' => 'not',
                'value' => '1'
            ];
        }

        foreach ( $this->arrFields as $strFieldname => $arrField ) {

            if ( in_array( $arrField['type'], [ 'select', 'checkbox', 'radio' ] ) ) {

                if ( isset( $arrField['optionsType'] ) && in_array( $arrField['optionsType'], [ 'useDbOptions', 'useForeignKey' ] )  ) {

                    if ( !$arrField['multiple'] ) {

                        $arrQuery['joins'][] = [

                            'multiple' => false,
                            'type' => 'LEFT JOIN',
                            'field' => $strFieldname,
                            'table' => $this->catalogTablename,
                            'onTable' => $arrField['dbTable'],
                            'onField' => $arrField['dbTableKey']
                        ];

                        $objChildFieldBuilder = new CatalogFieldBuilder();
                        $objChildFieldBuilder->initialize( $arrField['dbTable'] );

                        $this->mergeFields( $objChildFieldBuilder->getCatalogFields( true, null ), $arrField['dbTable'] );
                    }
                }
            }
        }

        if ( $arrCatalog['pTable'] ) {

            $arrQuery['joins'][] = [

                'field' => 'pid',
                'onField' => 'id',
                'multiple' => false,
                'table' => $this->catalogTablename,
                'onTable' => $arrCatalog['pTable']
            ];

            $objParentFieldBuilder = new CatalogFieldBuilder();
            $objParentFieldBuilder->initialize( $arrCatalog['pTable'] );

            $this->mergeFields( $objFieldBuilder->getCatalogFields( true, null ), $arrCatalog['pTable'] );
        }

        $this->import( 'SQLQueryBuilder' );

        $objEntity = $this->SQLQueryBuilder->execute( $arrQuery );

        if ( !$objEntity->numRows ) {

            return null;
        }

        $arrEntity = $objEntity->row();

        $this->Template->fields = $this->getTemplateFields();

        foreach ( $arrEntity as $strFieldname => $strValue ) {

            if ( isset( $this->arrFields[ $strFieldname ] ) ) {

                $arrField = $this->arrFields[ $strFieldname ];

                if ( $arrField['multiple'] && in_array( $arrField['optionsType'], [ 'useDbOptions', 'useForeignKey' ] ) ) {

                    $this->Template->{$strFieldname} = $this->getJoinedEntities( $strValue, $arrField );

                    continue;
                }

                $this->Template->{$strFieldname} = Toolkit::parseCatalogValue( $strValue, $arrField, $arrEntity );
            }
        }
    }


    protected function mergeFields( $arrFields, $strTablename ) {

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( is_numeric( $strFieldname ) ) {

                continue;
            }

            $this->arrFields[ $strTablename . ucfirst( $strFieldname ) ] = $arrField;
        }
    }


    protected function getTemplateFields() {

        $arrReturn = [];

        foreach ( $this->arrFields as $strFieldname => $arrField ) {

            $strLabel = $strFieldname;

            if ( is_array( $arrField['_dcFormat'] ) && isset( $arrField['_dcFormat']['label'] ) ) {

                $strLabel = $arrField['_dcFormat']['label'][0];
            }

            $arrReturn[ $strFieldname ] = $strLabel;
        }

        return $arrReturn;
    }


    protected function getJoinedEntities( $strValue, $arrField ) {

        $arrReturn = [];
        $arrOrderBy= Toolkit::parseStringToArray( $arrField['dbOrderBy'] );

        $arrQuery = [

            'table' => $arrField['dbTable'],
            'where' => [

                [
                    'operator' => 'findInSet',
                    'field' => $arrField['dbTableKey'],
                    'value' => explode( ',', $strValue )
                ]
            ],
            'orderBy' => []
        ];

        if ( is_array( $arrOrderBy ) && !empty( $arrOrderBy ) ) {

            foreach ( $arrOrderBy as $arrOrder ) {

                $arrQuery['orderBy'][] = [

                    'field' => $arrOrder['key'],
                    'order' => $arrOrder['value']
                ];
            }
        }

        $objEntities = $this->SQLQueryBuilder->execute( $arrQuery );

        if ( !$objEntities->numRows ) return $arrReturn;

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize( $arrField['dbTable'] );

        while ( $objEntities->next() ) {

            $arrReturn[] = Toolkit::parseCatalogValues( $objEntities->row(), $objFieldBuilder->getCatalogFields( true, null ) );
        }

        return $arrReturn;
    }
}