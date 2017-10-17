<?php

namespace CatalogManager;

class CatalogMasterEntity extends CatalogController {


    protected $strTable = '';
    protected $arrCatalog = [];
    protected $arrJoinFields = [];
    protected $arrCatalogFields = [];
    protected $blnJoinParent = false;


    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
        $this->import( 'SQLQueryBuilder' );
    }


    public function initialize( $strTablename, $arrJoinOnly = [], $blnJoinParent = false ) {

        $this->strTable = $strTablename;
        $this->blnJoinParent = $blnJoinParent;

        $objCatalog = $this->Database->prepare( sprintf( 'SELECT * FROM tl_catalog WHERE tablename = ?' ) )->limit(1)->execute( $this->strTable );

        if ( $objCatalog !== null ) {

            if ( $objCatalog->numRows ) $this->arrCatalog = $objCatalog->row();
        }

        $objFields = $this->Database->prepare( sprintf( 'SELECT * FROM tl_catalog_fields WHERE pid = ? AND invisible != "1" ORDER BY sorting ASC' ) )->execute( $objCatalog->id );

        if ( $objFields !== null ) {

            if ( $objFields->numRows ) {

                while ( $objFields->next() ) {

                    if ( !$objFields->fieldname ) continue;

                    $this->arrCatalogFields[ $objFields->fieldname ] = $objFields->row();

                    if ( !empty( $arrJoinOnly ) && !in_array( $objFields->fieldname, $arrJoinOnly ) ) continue;
                    if ( !in_array( $objFields->type, [ 'select', 'radio' ] ) ) continue;
                    if ( !in_array( $objFields->optionsType, [ 'useForeignKey', 'useDbOptions' ] ) ) continue;
                    if ( !$objFields->dbTable ) continue;

                    $this->arrJoinFields[] = $objFields->fieldname;
                }
            }
        }
    }


    public function getMasterEntity( $blnParseValues = true ) {

        $arrMaster = [];
        $strAlias = \Input::get('auto_item');

        if ( Toolkit::isEmpty( $strAlias ) ) return $arrMaster;

        $arrQuery = [

            'table' => $this->strTable,

            'pagination' => [

                'limit' => 1,
                'offset' => 0
            ],

            'joins' => [],

            'where' => [

                [
                    [
                        'field' => 'alias',
                        'value' => $strAlias,
                        'operator' => 'equal'
                    ],

                    [
                        'field' => 'id',
                        'operator' => 'equal',
                        'value' => (int)$strAlias,
                    ]
                ]
            ]

        ];

        if ( !empty( $this->arrJoinFields ) && is_array( $this->arrJoinFields ) ) {

            $arrQuery['joins'] = $this->joinFields();
        }

        if ( $this->arrCatalog['pTable'] && $this->blnJoinParent ) {

            if ( $this->Database->tableExists( $this->arrCatalog['pTable'] ) ) {

                $arrQuery['joins'][] = $this->joinParent();
            }
        }

        $objMaster = $this->SQLQueryBuilder->execute( $arrQuery );

        if ( !$blnParseValues ) return $objMaster->row();

        if ( $objMaster->numRows ) {

            $objFieldBuilder = new CatalogFieldBuilder();
            $objFieldBuilder->initialize(  $this->strTable );
            $arrFields = $objFieldBuilder->getCatalogFields( false, null, false, false );

            $arrMaster = Toolkit::parseCatalogValues( $objMaster->row(), $arrFields );
        }

        return $arrMaster;
    }


    protected function joinParent() {

        return [

            'onTable' => $this->arrCatalog['pTable'],
            'table' => $this->strTable,
            'multiple' => false,
            'onField' => 'id',
            'field' => 'pid'
        ];
    }


    protected function joinFields() {

        $arrReturn = [];

        foreach ( $this->arrJoinFields as $strFieldname ) {

            $arrField = $this->arrCatalogFields[ $strFieldname ];

            if ( !$arrField ) continue;

            if ( !$this->Database->tableExists( $arrField['dbTable'] ) ) continue;

            if ( $arrField['optionsType'] == 'useForeignKey' ) {

                $arrField['dbTableKey'] = 'id';
            }

            $arrJoin = [

                'onField' => $arrField['dbTableKey'],
                'onTable' => $arrField['dbTable'],
                'field' => $arrField['fieldname'],
                'table' => $this->strTable,
                'type' => 'LEFT JOIN',
                'multiple' => false,
            ];

            $arrReturn[] = $arrJoin;
        }

        return $arrReturn;
    }


    public function getCatalog() {

        return $this->arrCatalog;
    }


    public function getCatalogFields() {

        return $this->arrCatalogFields;
    }


    public function getJoinFields() {

        return $this->arrJoinFields;
    }
}