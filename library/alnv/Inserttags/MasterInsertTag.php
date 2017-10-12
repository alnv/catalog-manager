<?php

namespace CatalogManager;

class MasterInsertTag extends \Frontend {


    protected $arrCatalogFields = [];
    protected $blnJoinParent = false;
    protected $blnJoinFields = false;
    protected $arrJoinFields = [];
    protected $arrJoinOnly = [];
    protected $arrCatalog = [];
    protected $arrMaster = [];
    protected $strHash = '';
    protected $strTable;
    

    public function getInsertTagValue( $strTag ) {

        global $objPage;

        $arrTags = explode( '::', $strTag );

        if ( empty( $arrTags ) || !is_array( $arrTags ) ) {

            return false;
        }

        if ( isset( $arrTags[0] ) && $arrTags[0] == 'CTLG_MASTER' && $objPage->catalogUseMaster ) {

            $strDefaultValue = '';
            $blnParseValues = false;
            $blnPreventCache = false;
            $strFieldname = $arrTags[1];
            $this->strTable = $objPage->catalogMasterTable;

            if ( !$strFieldname || !$this->strTable ) return false;

            if ( isset( $arrTags[2] ) && strpos( $arrTags[2], '?' ) !== false ) {

                $arrChunks = explode('?', urldecode( $arrTags[2] ), 2 );
                $strSource = \StringUtil::decodeEntities( $arrChunks[1] );
                $strSource = str_replace( '[&]', '&', $strSource );
                $arrParams = explode( '&', $strSource );

                foreach ( $arrParams as $strParam ) {

                    list( $strKey, $strOption ) = explode( '=', $strParam );

                    switch ( $strKey ) {

                        case 'default':

                            $strDefaultValue = $strOption;

                            break;

                        case 'parse':

                            $blnPreventCache = $this->strHash != md5( $strSource );
                            $blnParseValues = $strOption ? true : false;

                            break;

                        case 'joinParent':

                            $blnPreventCache = $this->strHash != md5( $strSource );
                            $this->blnJoinParent = $strOption ? true : false;

                            break;

                        case 'joinFields':

                            $blnPreventCache = $this->strHash != md5( $strSource );
                            $this->blnJoinFields = $strOption ? true : false;

                            break;

                        case 'joinOnly':

                            $blnPreventCache = $this->strHash != md5( $strSource );
                            $arrFields = explode( ',', $strOption );

                            if ( !empty( $arrFields ) && is_array( $arrFields ) ) {

                                $this->arrJoinOnly = $arrFields;
                            }

                            break;
                    }
                }

                $this->strHash = md5( $strSource );
            }

            else {

                $strDefaultValue = Toolkit::isEmpty( $arrTags[2] ) ? '' : $arrTags[2];
            }

            if ( empty( $this->arrCatalog ) ) {

                $this->initialize();
            }

            if ( empty( $this->arrMaster ) || $blnPreventCache ) {

                $this->getMasterEntity( $blnParseValues );
            }

            if ( Toolkit::isEmpty( $this->arrMaster[ $strFieldname ] ) && !Toolkit::isEmpty( $strDefaultValue ) ) {

                return $strDefaultValue;
            }

            $varValue = Toolkit::isEmpty( $this->arrMaster[ $strFieldname ] ) ? '' : $this->arrMaster[ $strFieldname ];

            if ( is_array( $varValue ) ) {

                $strKeyname = $arrTags[3] ?: '';

                if ( $strKeyname && isset( $varValue[ $strKeyname ] ) ) {

                    $varValue = $varValue[ $strKeyname ];
                }
            }

            return $varValue;
        }

        return false;
    }


    protected function initialize() {

        $objCatalog = $this->Database->prepare( sprintf( 'SELECT * FROM tl_catalog WHERE tablename = ?' ) )->limit(1)->execute( $this->strTable );

        if ( $objCatalog !== null ) {

            if ( $objCatalog->numRows ) {

                $this->arrCatalog = $objCatalog->row();
            }
        }

        $objFields = $this->Database->prepare( sprintf( 'SELECT * FROM tl_catalog_fields WHERE pid = ? AND invisible != "1" ORDER BY sorting ASC' ) )->execute( $objCatalog->id );

        if ( $objFields !== null ) {

            if ( $objFields->numRows ) {

                while ( $objFields->next() ) {

                    if ( !$objFields->fieldname ) continue;

                    $this->arrCatalogFields[ $objFields->fieldname ] = $objFields->row();

                    if ( !empty( $this->arrJoinOnly ) && !in_array( $objFields->fieldname, $this->arrJoinOnly ) ) continue;
                    if ( !in_array( $objFields->type, [ 'select', 'radio' ] ) ) continue;
                    if ( !in_array( $objFields->optionsType, [ 'useForeignKey', 'useDbOptions' ] ) ) continue;
                    if ( !$objFields->dbTable ) continue;

                    $this->arrJoinFields[] = $objFields->fieldname;
                }
            }
        }

        unset( $objCatalog );
        unset( $objFields );
    }


    protected function getMasterEntity( $blnParseValues ) {

        $this->import( 'SQLQueryBuilder' );
        $strAlias = \Input::get('auto_item');

        if ( Toolkit::isEmpty( $strAlias ) ) $this->arrMaster[ $this->strTable ] = [];

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

        if ( !empty( $this->arrJoinFields ) && is_array( $this->arrJoinFields ) && $this->blnJoinFields ) {

            $arrQuery['joins'] = $this->joinFields();
        }

        if ( $this->arrCatalog['pTable'] && $this->blnJoinParent ) {

            if ( $this->Database->tableExists( $this->arrCatalog['pTable'] ) ) {

                $arrQuery['joins'][] = $this->joinParent();
            }
        }

        $objMaster = $this->SQLQueryBuilder->execute( $arrQuery );

        if ( $objMaster->numRows ) {

            $this->arrMaster = $objMaster->row();

            if ( $blnParseValues ) {

                $objFieldBuilder = new CatalogFieldBuilder();
                $objFieldBuilder->initialize(  $this->strTable );
                $arrFields = $objFieldBuilder->getCatalogFields( false, null, false, false );

                $this->arrMaster = Toolkit::parseCatalogValues( $this->arrMaster, $arrFields );
            }
        }
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
}