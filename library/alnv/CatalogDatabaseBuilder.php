<?php

namespace CatalogManager;

class CatalogDatabaseBuilder extends CatalogController {


    protected $arrCatalog = [];
    protected $strTablename = null;
    protected $arrPermissionColumns = [];

    protected $arrTableColumns = [

        'stop' => "varchar(16) NOT NULL default ''",
        'start' => "varchar(16) NOT NULL default ''",
        'invisible' => "char(1) NOT NULL default ''",
        'title' => "varchar(255) NOT NULL default ''",
        'alias' => "varchar(255) NOT NULL default ''",
        'pid' => "int(10) unsigned NOT NULL default '0'",
        'id' => "int(10) unsigned NOT NULL auto_increment",
        'tstamp' => "int(10) unsigned NOT NULL default '0'",
        'sorting' => "int(10) unsigned NOT NULL default '0'"
    ];

    
    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
    }


    public function initialize( $strTablename, $arrCatalog = null ) {

        $this->strTablename = $strTablename;

        if ( is_null( $arrCatalog ) ) {

            $objCatalog = $this->Database->prepare( 'SELECT * FROM tl_catalog WHERE `tablename` = ? AND tstamp > 0' )->limit(1)->execute( $strTablename );

            if ( $objCatalog->numRows ) $arrCatalog = $objCatalog->row();
        }

        if ( !empty( $arrCatalog ) && is_array( $arrCatalog ) ) {

            $this->arrCatalog = Toolkit::parseCatalog( $arrCatalog );
        }

        $this->arrPermissionColumns = [

            [
                'type' => 'default',
                'table' => 'tl_user',
                'field' => $this->strTablename . 'p'
            ],

            [
                'type' => 'extended',
                'table' => 'tl_user',
                'field' => $this->strTablename
            ],

            [
                'type' => 'default',
                'table' => 'tl_user_group',
                'field' => $this->strTablename . 'p'
            ],

            [
                'type' => 'extended',
                'table' => 'tl_user_group',
                'field' => $this->strTablename
            ],

            [
                'type' => 'default',
                'table' => 'tl_member_group',
                'field' => $this->strTablename . 'p'
            ],

            [
                'type' => 'extended',
                'table' => 'tl_member_group',
                'field' => $this->strTablename
            ]
        ];
    }


    public function createTable() {

        $arrColumns = $this->arrTableColumns;
        $objSQLBuilder = new SQLBuilder();

        if ( !$this->arrCatalog['mode'] ) {

            unset( $arrColumns['sorting'] );
        }

        if ( !$this->hasOperator( 'invisible' ) ) {

            unset( $arrColumns['invisible'] );
            unset( $arrColumns['start'] );
            unset( $arrColumns['stop'] );
        }

        if ( !$this->hasParent() ) {

            unset( $arrColumns['pid'] );
        }

        $objSQLBuilder->createSQLCreateStatement( $this->strTablename, $arrColumns );
        $this->checkPermissionFields( 'create' );
    }


    public function renameTable( $strNewTablename ) {

        $objSQLBuilder = new SQLBuilder();
        $objSQLBuilder->createSQLRenameTableStatement( $strNewTablename, $this->strTablename );
        $this->checkDependencies( $strNewTablename );
        $this->checkPermissionFields( 'rename', $strNewTablename );
    }


    public function dropTable() {

        $objSQLBuilder = new SQLBuilder();
        $objSQLBuilder->createSQLDropTableStatement( $this->strTablename );
        $this->checkPermissionFields( 'drop' );
    }
    
    
    public function tableCheck() {

        $objSQLBuilder = new SQLBuilder();

        if ( !$this->Database->tableExists( $this->strTablename ) ) return null;

        if ( $this->arrCatalog['mode'] ) {

            $objSQLBuilder->alterTableField( $this->strTablename, 'sorting' , $this->arrTableColumns['sorting'] );
        }

        if ( $this->hasOperator( 'invisible' ) ) {

            $objSQLBuilder->alterTableField( $this->strTablename, 'stop' , $this->arrTableColumns['stop'] );
            $objSQLBuilder->alterTableField( $this->strTablename, 'start' , $this->arrTableColumns['start'] );
            $objSQLBuilder->alterTableField( $this->strTablename, 'invisible', $this->arrTableColumns['invisible'] );
        }

        if ( $this->hasParent() ) {

            $objSQLBuilder->alterTableField( $this->strTablename, 'pid' , $this->arrTableColumns['pid'] );
        }

        $this->checkPermissionFields( 'create' );
    }


    protected function hasOperator( $strOperator ) {

        if ( !empty( $this->arrCatalog['operations'] ) && is_array( $this->arrCatalog['operations'] ) ) {

            return in_array( $strOperator, $this->arrCatalog['operations'] );
        }

        return false;
    }


    protected function hasParent() {

        if ( $this->arrCatalog['pTable'] ) {

            return true;
        }

        if ( in_array( $this->arrCatalog['mode'], [ '4', '5' ] ) ) {

            return true;
        }

        return false;
    }


    protected function checkDependencies( $strNewTable ) {

        $objSQLBuilder = new SQLBuilder();
        $objCatalogs = $this->Database->prepare( 'SELECT * FROM tl_catalog WHERE `id` != ?' )->execute( $this->arrCatalog['id'] );

        if ( $objCatalogs->numRows ) {

            while ( $objCatalogs->next() ) {

                $arrCatalog = $objCatalogs->row();
                $arrCTables = deserialize( $arrCatalog['cTables'] );

                if ( !empty( $arrCTables ) && is_array( $arrCTables ) ) {

                    foreach ( $arrCTables as $intIndex => $strTable ) {

                        if ( $strTable == $this->strTablename ) {

                            $arrCTables[ $intIndex ] = $strNewTable;
                        }
                    }

                    $arrCatalog['cTables'] = serialize( $arrCTables );
                }

                if ( $arrCatalog['pTable'] ) {

                    if ( $arrCatalog['pTable'] == $this->strTablename ) {

                        $arrCatalog['pTable'] = $strNewTable;
                    }
                }

                $objSQLBuilder->updateTableFieldByID( $arrCatalog['id'], 'tl_catalog', $arrCatalog );
            }
        }
    }


    protected function checkPermissionFields( $strEvent = '', $strNewTablename = '' ) {

        if ( Toolkit::isEmpty( $this->arrCatalog['permissionType'] ) ) {

            $this->resetPermissionFields();

            return null;
        }

        switch ( $strEvent ) {

            case 'create':

                $this->addPermissionFields();

                break;

            case 'rename':

                $this->renamePermissionFields( $strNewTablename );

                break;

            case 'drop':

                $this->dropPermissionFields();

                break;
        }
    }


    protected function addPermissionFields() {

        $objSQLBuilder = new SQLBuilder();

        $this->getPermissionColumns( function( $arrPermissionColumn ) use( $objSQLBuilder ) {

            $objSQLBuilder->alterTableField( $arrPermissionColumn['table'], $arrPermissionColumn['field'], 'blob NULL' );
        });
    }


    protected function dropPermissionFields() {

        $objSQLBuilder = new SQLBuilder();

        $this->getPermissionColumns( function( $arrPermissionColumn ) use ( $objSQLBuilder ) {

            $objSQLBuilder->dropTableField( $arrPermissionColumn['table'] , $arrPermissionColumn['field'] );
        });
    }


    protected function renamePermissionFields( $strNewTablename ) {

        $objSQLBuilder = new SQLBuilder();

        $this->getPermissionColumns( function( $arrPermissionColumn ) use ( $objSQLBuilder ) {

            $objSQLBuilder->createSQLRenameFieldnameStatement( $arrPermissionColumn['table'], $arrPermissionColumn['field'], $arrPermissionColumn['newField'], 'blob NULL' );

        }, $strNewTablename );
    }


    protected function resetPermissionFields() {

        $objSQLBuilder = new SQLBuilder();

        $this->getPermissionColumns( function( $arrPermissionColumn ) use ( $objSQLBuilder ) {

            if ( $objSQLBuilder->Database->fieldExists( $arrPermissionColumn['field'], $arrPermissionColumn['table'] ) ) {

                $objSQLBuilder->dropTableField( $arrPermissionColumn['table'] , $arrPermissionColumn['field'] );
            }
        });
    }


    protected function getPermissionColumns( $arrCallback = null, $strNewTable = '' ) {

        foreach ( $this->arrPermissionColumns as $arrPermissionColumn ) {

            if ( $this->arrCatalog['permissionType'] == 'default' &&  $arrPermissionColumn['type'] == 'extended' ) {

                continue;
            }

            if ( !Toolkit::isEmpty( $strNewTable ) ) {

                $arrPermissionColumn['newField'] = $strNewTable;
            }

            if ( is_array( $arrCallback ) ) {

                $this->import( $arrCallback[0] );
                $this->{$arrCallback[0]}->{$arrCallback[1]}( $arrPermissionColumn );
            }

            elseif( is_callable( $arrCallback ) ) {

                $arrCallback( $arrPermissionColumn );
            }
        }
    }
}