<?php

namespace Alnv\CatalogManagerBundle;

class SQLQueryHelper extends CatalogController {


    public function __construct() {

        parent::__construct();

        $this->import( 'SQLQueryBuilder' );
        $this->import( 'CatalogFieldBuilder' );
    }


    public function getCatalogTableItemByID( $strTablename, $strID ) {
        
        if ( !$strTablename || !$strID ) return [];

        return $this->SQLQueryBuilder->Database->prepare( sprintf( 'SELECT * FROM %s WHERE id = ?', $strTablename ) )->limit( 1 )->execute( $strID )->row();
    }


    public function getCatalogs() {

        return $this->SQLQueryBuilder->execute([

            'table' => 'tl_catalog'
        ]);
    }


    public function getCatalogByTablename( $strTablename ) {

        if ( !$strTablename ) return [];

        return $this->SQLQueryBuilder->execute([

            'table' => 'tl_catalog',

            'pagination' => [

                'limit' => 1,
                'offset' => 0,
            ],

            'where' => [

                [
                    'operator' => 'equal',
                    'field' => 'tablename',
                    'value' => $strTablename
                ]
            ]

        ])->row();
    }

    
    public function getCatalogFieldsByCatalogID( $strID, $arrCallback = [], $arrFields = [] ) {
        
        if ( !$strID ) return $arrFields;

        $objFields = $this->SQLQueryBuilder->execute([

            'table' => 'tl_catalog_fields',

            'orderBy' => [

                [
                    'order' => 'ASC',
                    'field' => 'sorting'
                ]
            ],

            'where' => [

                [
                    'field' => 'pid',
                    'value' => $strID,
                    'operator' => 'equal'
                ],

                [
                    'value' => '',
                    'operator' => 'equal',
                    'field' => 'invisible'
                ]
            ]

        ]);

        $intIndex = 0;
        $intCount = $objFields->count();

        while ( $objFields->next() ) {

            $arrFields[ $objFields->id ] = $objFields->row();

            if ( !empty( $arrCallback ) && is_array( $arrCallback ) ) {

                $this->import( $arrCallback[0] );
                $arrFields[ $objFields->id ] = $this->{$arrCallback[0]}->{$arrCallback[1]}( $arrFields[ $objFields->id ], $objFields->fieldname, $intIndex, $intCount );
            }

            elseif ( is_callable( $arrCallback ) ) {

                $arrFields[ $objFields->id ] = $arrCallback( $arrFields[ $objFields->id ], $objFields->fieldname, $intIndex, $intCount );
            }

            if ( $arrFields[ $objFields->id ] == null ) {

                continue;
            }

            $intIndex++;
        }

        return $arrFields;
    }


    public function getCatalogFieldsByCatalogTablename( $strTablename, $arrFields = [], $blnUseTablePrefix = false, &$staticFields = [] ) {

        if ( !$strTablename ) return $arrFields;

        $this->CatalogFieldBuilder->initialize( $strTablename );

        $arrCatalogFields = $this->CatalogFieldBuilder->getCatalogFields( false, null );

        if ( !empty( $arrCatalogFields ) && is_array( $arrCatalogFields ) ) {

            foreach ( $arrCatalogFields as $strAlias => $arrCatalogField ) {

                if ( $blnUseTablePrefix ) $strAlias = $strTablename . ucfirst( $strAlias );

                if ( in_array( $arrCatalogField['type'], [ 'map', 'message' ] ) ) {

                    $staticFields[] = $strAlias;
                }

                $arrFields[ $strAlias ] = $arrCatalogField;
            }
        }

        return $arrFields;
    }
}