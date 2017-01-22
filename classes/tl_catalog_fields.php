<?php

namespace CatalogManager;

class tl_catalog_fields extends \Backend {

    public function createFieldOnSubmit( \DataContainer $dc ) {

        $strID = $dc->activeRecord->pid;
        $objSQLBuilder = new SQLBuilder();
        $strIndex = $dc->activeRecord->useIndex;
        $strStatement = DCAHelper::$arrSQLStatements[ $dc->activeRecord->statement ];
        $arrCatalog = $this->Database->prepare('SELECT * FROM tl_catalog WHERE id = ? LIMIT 1')->execute( $strID )->row();
        
        if ( !$this->Database->fieldExists( $dc->activeRecord->fieldname, $arrCatalog['tablename'] ) ) {

            if ( in_array( $dc->activeRecord->type , DCAHelper::$arrForbiddenInputTypes ) ) {

                return null;
            }

            $objSQLBuilder->alterTableField( $arrCatalog['tablename'], $dc->activeRecord->fieldname, $strStatement );

            if ( $strIndex ) {

                $objSQLBuilder->addIndex( $arrCatalog['tablename'], $dc->activeRecord->fieldname, $strIndex );
            }
        }

        else {
            
            if ( in_array( $dc->activeRecord->type , DCAHelper::$arrForbiddenInputTypes ) ) {

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

    public function renameFieldname( $varValue, \DataContainer $dc ) {

        if ( !$varValue || !$dc->activeRecord->fieldname || $dc->activeRecord->fieldname == $varValue ) {

            return $varValue;
        }

        $strStatement = DCAHelper::$arrSQLStatements[ $dc->activeRecord->statement ];
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

        return DCAHelper::$arrSQLStatements;
    }

    public function getCatalogFieldList( $arrRow ) {

        return $arrRow['title'];
    }
}