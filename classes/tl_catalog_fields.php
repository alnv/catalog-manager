<?php

namespace CatalogManager;

class tl_catalog_fields extends \Backend {

    private $arrFieldCache = [];

    public function __construct() {

        parent::__construct();

        $this->import( 'DCABuilderHelper' );
    }

    public function checkPermission() {

        $objDCAPermission = new DCAPermission();
        $objDCAPermission->checkPermissionByParent( 'tl_catalog_fields' , 'tl_catalog', 'catalog', 'catalogp' );
    }

    public function createFieldOnSubmit( \DataContainer $dc ) {

        $strID = $dc->activeRecord->pid;
        $objSQLBuilder = new SQLBuilder();
        $strIndex = $dc->activeRecord->useIndex;
        $strStatement = $this->DCABuilderHelper->arrSQLStatements[ $dc->activeRecord->statement ];
        $arrCatalog = $this->Database->prepare('SELECT * FROM tl_catalog WHERE id = ? LIMIT 1')->execute( $strID )->row();

        if ( in_array( $dc->activeRecord->fieldname , $this->DCABuilderHelper->arrReservedFields ) ) {

            throw new \Exception( sprintf( 'Fieldname "%s" is not allowed.', $dc->activeRecord->fieldname ) );
        }

        if ( !$this->Database->fieldExists( $dc->activeRecord->fieldname, $arrCatalog['tablename'] ) ) {

            if ( in_array( $dc->activeRecord->type , $this->DCABuilderHelper->arrForbiddenInputTypes ) ) {

                return null;
            }

            $objSQLBuilder->alterTableField( $arrCatalog['tablename'], $dc->activeRecord->fieldname, $strStatement );

            if ( $strIndex ) {

                $objSQLBuilder->addIndex( $arrCatalog['tablename'], $dc->activeRecord->fieldname, $strIndex );
            }
        }

        else {
            
            if ( in_array( $dc->activeRecord->type , $this->DCABuilderHelper->arrForbiddenInputTypes ) ) {

                $this->dropFieldOnDelete( $dc );

                return null;
            }

            $arrColumns = $objSQLBuilder->showColumns( $arrCatalog['tablename'] );

            if ( !$arrColumns[ $dc->activeRecord->fieldname ] ) {

                return null;
            }

            if ( $arrColumns[ $dc->activeRecord->fieldname ]['statement'] !== $strStatement ) {

                $objSQLBuilder->modifyTableField( $arrCatalog['tablename'], $dc->activeRecord->fieldname, $strStatement );
            }

            if ( $strIndex && $strIndex !== $arrColumns[ $dc->activeRecord->fieldname ]['index'] ) {

                $objSQLBuilder->dropIndex( $arrCatalog['tablename'], $dc->activeRecord->fieldname );
                $objSQLBuilder->addIndex( $arrCatalog['tablename'], $dc->activeRecord->fieldname, $strIndex );
            }

            if ( !$strIndex && $arrColumns[ $dc->activeRecord->fieldname ]['index'] ) {

                $objSQLBuilder->dropIndex( $arrCatalog['tablename'], $dc->activeRecord->fieldname );
            }
        }
    }

    public function checkUniqueValue( $varValue, \DataContainer $dc ) {

        $objFieldname = $this->Database->prepare('SELECT pid, id FROM tl_catalog_fields WHERE fieldname = ? AND id != ?')->limit(1)->execute( $varValue, $dc->activeRecord->id );

        if ( $objFieldname->numRows && $objFieldname->pid == $dc->activeRecord->pid ) {

            throw new \Exception('This fieldname already exist.');
        }

        return $varValue;
    }

    public function getFilesTypes() {

        return [ 'image', 'file' ];
    }

    public function getTextFieldsByParentID() {

        $arrReturn = [ 'title' ];

        if ( !empty( $this->arrFieldCache ) && is_array( $this->arrFieldCache ) ) {

            return $this->arrFieldCache;
        }

        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT pid FROM tl_catalog_fields WHERE id = ? )' )->execute( \Input::get('id') );

        while ( $objCatalogFields->next() ) {

            if ( $objCatalogFields->type !== 'text' ) {

                continue;
            }

            $arrReturn[] = $objCatalogFields->fieldname;
        }

        $this->arrFieldCache = $arrReturn;

        return $arrReturn;
    }

    public function renameFieldname( $varValue, \DataContainer $dc ) {

        if ( !$varValue || !$dc->activeRecord->fieldname || $dc->activeRecord->fieldname == $varValue ) {

            return $varValue;
        }

        $strStatement = $this->DCABuilderHelper->arrSQLStatements[ $dc->activeRecord->statement ];
        $objCatalog = $this->Database->prepare( 'SELECT tablename FROM tl_catalog WHERE id = ? LIMIT 1' )->execute( $dc->activeRecord->pid );

        if ( !$objCatalog->count() ) {

            return $varValue;
        }

        $strTable = $objCatalog->row()['tablename'];

        if ( $this->Database->tableExists( $strTable ) ) {

            $objSQLBuilder = new SQLBuilder();
            $objSQLBuilder->createSQLRenameFieldnameStatement( $strTable, $dc->activeRecord->fieldname, $varValue, $strStatement );
        }

        return $varValue;
    }

    public function checkFieldname( $varValue ) {

        return Toolkit::parseConformSQLValue( $varValue );
    }

    public function dropFieldOnDelete( \DataContainer $dc ) {

        $strID = $dc->activeRecord->pid;
        $arrCatalog = $this->Database->prepare('SELECT * FROM tl_catalog WHERE id = ? LIMIT 1')->execute( $strID )->row();

        $objSQLBuilder = new SQLBuilder();
        $objSQLBuilder->dropTableField( $arrCatalog['tablename'], $dc->activeRecord->fieldname );
    }

    public function getTables() {

        return $this->Database->listTables( null );
    }

    public function getColumnsByDbTable( \DataContainer $dc ) {

        $strTable = $dc->activeRecord->dbTable;

        if ( $strTable && $this->Database->tableExists( $strTable ) ) {

            $arrColumns = $this->Database->listFields( $strTable );

            return Toolkit::parseColumns( $arrColumns );
        }

        return [];
    }

    public function getFieldTypes() {

        return [ 'text', 'date', 'radio', 'hidden', 'number', 'select', 'upload', 'message', 'checkbox', 'textarea', 'fieldsetStart', 'fieldsetStop' ];
    }

    public function getIndexes() {

        return [ 'index', 'unique' ];
    }

    public function getRGXPTypes( \DataContainer $dc ) {

        if ( $dc->activeRecord->type && $dc->activeRecord->type == 'number') {

            return [ 'digit', 'natural', 'prcnt' ];
        }

        if ( $dc->activeRecord->type && $dc->activeRecord->type == 'date') {

            return [ 'date', 'time', 'datim' ];
        }

        return [ 'url', 'time', 'date', 'alias', 'alnum', 'alpha', 'datim', 'digit', 'email', 'extnd', 'phone', 'prcnt', 'locale', 'emails', 'natural', 'friendly', 'language', 'folderalias' ];
    }

    public function getRichTextEditor() {

        return [ 'tinyMCE', 'tinyFlash' ];
    }

    public function getTLClasses() {

        return [ 'w50', 'long', 'm12', 'clr' ];
    }

    public function getFieldFlags() {

        return [ '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12' ];
    }

    public function getSQLStatements() {

        return $this->DCABuilderHelper->arrSQLStatements;
    }

    public function getCatalogFieldList( $arrRow ) {

        return $arrRow['title'];
    }
}