<?php

namespace CatalogManager;

class CatalogDatabaseBuilder extends CatalogController {


    protected $arrColumn = [];
    protected $arrCatalog = [];
    protected $strTablename = null;
    protected $blnCoreTable = false;
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
        $this->import( 'Automator' );
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
                'postfix' => true,
                'type' => 'default',
                'table' => 'tl_user',
                'field' => $this->strTablename . 'p'
            ],

            [
                'postfix' => false,
                'type' => 'extended',
                'table' => 'tl_user',
                'field' => $this->strTablename
            ],

            [
                'postfix' => true,
                'type' => 'default',
                'table' => 'tl_user_group',
                'field' => $this->strTablename . 'p'
            ],

            [
                'postfix' => false,
                'type' => 'extended',
                'table' => 'tl_user_group',
                'field' => $this->strTablename
            ],

            [
                'postfix' => true,
                'type' => 'permanent',
                'table' => 'tl_member_group',
                'field' => $this->strTablename . 'p'
            ],

            [
                'postfix' => false,
                'type' => 'permanent',
                'table' => 'tl_member_group',
                'field' => $this->strTablename
            ]
        ];

        $this->blnCoreTable = Toolkit::isCoreTable( $strTablename );
        if ( $this->blnCoreTable ) $this->arrCatalog['permissionType'] = '';
    }


    public function setColumn( $arrColumn ) {

        if ( !empty( $arrColumn ) && is_array( $arrColumn ) ) {

            $this->arrColumn = $arrColumn;
        }
    }


    public function createTable() {

        $arrColumns = $this->arrTableColumns;
        $objSQLBuilder = new SQLBuilder();

        if ( !in_array( $this->arrCatalog['mode'], Toolkit::$arrRequireSortingModes ) ) {

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

        if ( !$this->blnCoreTable ) {

            $objSQLBuilder->createSQLCreateStatement( $this->strTablename, $arrColumns );
        }

        $this->checkPermissionFields( 'create' );
        $this->clearCache();
    }


    public function renameTable( $strNewTablename ) {

        if ( !$this->blnCoreTable ) {

            $objSQLBuilder = new SQLBuilder();
            $objSQLBuilder->createSQLRenameTableStatement( $strNewTablename, $this->strTablename );
            $this->checkDependencies( $strNewTablename );
        }

        $this->checkPermissionFields( 'rename', $strNewTablename );
        $this->clearCache();
    }


    public function dropTable() {

        if ( !$this->blnCoreTable ) {

            $objSQLBuilder = new SQLBuilder();
            $objSQLBuilder->createSQLDropTableStatement( $this->strTablename );
        }

        $this->checkPermissionFields( 'drop' );
        $this->clearCache();
    }
    
    
    public function tableCheck() {

        $objSQLBuilder = new SQLBuilder();

        if ( !$this->Database->tableExists( $this->strTablename ) ) return null;

        if ( in_array( $this->arrCatalog['mode'], Toolkit::$arrRequireSortingModes ) && !$this->blnCoreTable ) {

            $objSQLBuilder->alterTableField( $this->strTablename, 'sorting' , $this->arrTableColumns['sorting'] );
        }

        if ( $this->hasOperator( 'invisible' ) && !$this->blnCoreTable ) {

            $objSQLBuilder->alterTableField( $this->strTablename, 'stop' , $this->arrTableColumns['stop'] );
            $objSQLBuilder->alterTableField( $this->strTablename, 'start' , $this->arrTableColumns['start'] );
            $objSQLBuilder->alterTableField( $this->strTablename, 'invisible', $this->arrTableColumns['invisible'] );
        }

        if ( $this->hasParent() && !$this->blnCoreTable ) {

            $objSQLBuilder->alterTableField( $this->strTablename, 'pid' , $this->arrTableColumns['pid'] );
        }

        $this->checkPermissionFields( 'create' );
        $this->clearCache();
    }


    public function createColumn() {

        if ( in_array( $this->arrColumn['type'], Toolkit::excludeFromDc() ) ) return null;

        $objSQLBuilder = new SQLBuilder();
        $strSQLData = Toolkit::getSqlDataType( $this->arrColumn['statement'] );
        $objSQLBuilder->alterTableField( $this->strTablename, $this->arrColumn['fieldname'], $strSQLData );

        if ( $this->arrColumn['useIndex'] ) {

            $objSQLBuilder->addIndex( $this->strTablename, $this->arrColumn['fieldname'], $this->arrColumn['useIndex'] );
        }

        $this->clearCache();
    }


    public function renameColumn( $strNewFieldname ) {

        $objSQLBuilder = new SQLBuilder();
        $strSQLData = Toolkit::getSqlDataType( $this->arrColumn['statement'] );
        $objSQLBuilder->createSQLRenameFieldnameStatement( $this->strTablename, $this->arrColumn['fieldname'], $strNewFieldname, $strSQLData );

        $this->clearCache();
    }


    public function dropColumn() {

        if ( in_array( $this->arrColumn['fieldname'], Toolkit::customizeAbleFields() ) ) {

            return null;
        }

        $objSQLBuilder = new SQLBuilder();
        $objSQLBuilder->dropTableField( $this->strTablename, $this->arrColumn['fieldname'] );

        $this->clearCache();
    }


    public function columnCheck() {

        if ( in_array( $this->arrColumn['type'], Toolkit::excludeFromDc() ) && $this->Database->fieldExists( $this->arrColumn['fieldname'], $this->strTablename ) ) {

            $this->dropColumn();

            return null;
        }

        $objSQLBuilder = new SQLBuilder();
        $arrColumns = $objSQLBuilder->showColumns( $this->strTablename );
        $strSQLData = Toolkit::getSqlDataType( $this->arrColumn['statement'] );

        if ( isset( $arrColumns[ $this->arrColumn['fieldname'] ] ) ) {

            $arrColumnData = $arrColumns[ $this->arrColumn['fieldname'] ];
        }

        else {

            $this->createColumn();

            return null;
        }

        if ( $arrColumnData['statement'] !== $strSQLData ) {

            $objSQLBuilder->modifyTableField( $this->strTablename, $this->arrColumn['fieldname'], $strSQLData );
        }

        if ( !$this->arrColumn['useIndex'] && $arrColumnData['index'] ) {

            $objSQLBuilder->dropIndex( $this->strTablename, $this->arrColumn['fieldname'] );
        }

        if ( $this->arrColumn['useIndex'] && $this->arrColumn['useIndex'] !== $arrColumnData['index'] ) {

            $objSQLBuilder->addIndex( $this->strTablename, $this->arrColumn['fieldname'], $this->arrColumn['useIndex'] );
        }

        $this->clearCache();
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

        if ( in_array( $this->arrCatalog['mode'], Toolkit::$arrRequireSortingModes ) ) {

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

            if ( Toolkit::isEmpty( $this->arrCatalog['permissionType'] ) && in_array( $arrPermissionColumn['type'], [ 'default', 'extended' ] ) ) return null;
            if ( $this->arrCatalog['permissionType'] == 'default' && $arrPermissionColumn['type'] == 'extended' ) return null;

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

            if ( Toolkit::isEmpty( $this->arrCatalog['permissionType'] ) && in_array( $arrPermissionColumn['type'], [ 'default', 'extended' ] ) ) return null;
            if ( $this->arrCatalog['permissionType'] == 'default' && $arrPermissionColumn['type'] == 'extended' ) return null;

            $objSQLBuilder->createSQLRenameFieldnameStatement( $arrPermissionColumn['table'], $arrPermissionColumn['field'], $arrPermissionColumn['newField'], 'blob NULL' );

        }, $strNewTablename );
    }


    protected function getPermissionColumns( $arrCallback = null, $strNewTable = '' ) {

        foreach ( $this->arrPermissionColumns as $arrPermissionColumn ) {

            if ( !Toolkit::isEmpty( $strNewTable ) ) $arrPermissionColumn['newField'] = $strNewTable . ( $arrPermissionColumn['postfix'] ? 'p' : '' );

            if ( is_array( $arrCallback ) ) {

                $this->import( $arrCallback[0] );
                $this->{$arrCallback[0]}->{$arrCallback[1]}( $arrPermissionColumn );
            }

            elseif( is_callable( $arrCallback ) ) {

                $arrCallback( $arrPermissionColumn );
            }
        }
    }


    protected function clearCache() {

        if ( version_compare( VERSION, '4.6', '<' ) ) {

            $this->Automator->purgeInternalCache();
        }
    }
}