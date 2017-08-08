<?php

namespace CatalogManager;

class MasterInsertTag extends \Frontend {


    protected $arrMaster = [];
    

    public function getInsertTagValue( $strTag ) {

        global $objPage;

        $arrTags = explode( '::', $strTag );

        if ( empty( $arrTags ) || !is_array( $arrTags ) ) {

            return false;
        }

        if ( isset( $arrTags[0] ) && $arrTags[0] == 'CTLG_MASTER' && $objPage->catalogUseMaster ) {

            $strFieldname = $arrTags[1];
            $strDefaultValue = $arrTags[2] ? $arrTags[2] : '';

            if ( !$strFieldname || !$objPage->catalogMasterTable ) return false;

            $this->getMasterEntity( $objPage->catalogMasterTable );

            if ( Toolkit::isEmpty( $this->arrMaster[ $objPage->catalogMasterTable ][ $strFieldname ] ) && !Toolkit::isEmpty( $strDefaultValue ) ) {

                return $strDefaultValue;
            }

            return $this->arrMaster[ $objPage->catalogMasterTable ][ $strFieldname ] ? $this->arrMaster[ $objPage->catalogMasterTable ][ $strFieldname ] : '';
        }

        return false;
    }


    protected function getMasterEntity( $strTable ) {

        $strAlias = \Input::get('auto_item');

        if ( !empty( $this->arrMaster[ $strTable ] ) && is_array( $this->arrMaster[ $strTable ] ) ) {

            return null;
        }

        if ( Toolkit::isEmpty( $strAlias ) ) $this->arrMaster[ $strTable ] = [];

        $arrMaster = [];
        $this->import( 'SQLQueryBuilder' );

        $arrQuery = [

            'table' => $strTable,

            'pagination' => [

                'limit' => 1,
                'offset' => 0
            ],

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

        $strPTable = '';
        $objCatalog = $this->Database->prepare( sprintf( 'SELECT * FROM tl_catalog WHERE tablename = ?' ) )->limit(1)->execute( $strTable );

        if ( $objCatalog->numRows ) {

            if ( !Toolkit::isEmpty( $objCatalog->pTable ) ) {

                $strPTable = $objCatalog->pTable;
            }
        }

        if ( $strPTable ) {

            if ( $this->Database->tableExists( $strPTable ) ) {

                $arrQuery['joins'] = [

                    [
                        'onTable' => $strPTable,
                        'table' => $strTable,
                        'multiple' => false,
                        'onField' => 'id',
                        'field' => 'pid'
                    ]
                ];
            }
        }

        $objMaster = $this->SQLQueryBuilder->execute( $arrQuery );
        
        if ( $objMaster->numRows ) $arrMaster = $objMaster->row();

        $this->arrMaster[ $strTable ] = $arrMaster;
    }
}