<?php

namespace CatalogManager;

class tl_catalog_fields extends \Backend {

    
    private $arrFieldsCache = [];
    private $arrTextFieldsCache = [];

    
    public function __construct() {

        parent::__construct();
    }

    
    public function checkPermission() {

        $objDCAPermission = new DCAPermission();
        $objDCAPermission->checkPermissionByParent( 'tl_catalog_fields' , 'tl_catalog', 'catalog', 'catalogp' );
    }


    public function changeGlobals() {

        if ( \Input::get( 'do' ) && \Input::get( 'do' ) == 'catalog-manager' ) {

            $GLOBALS['TL_LANG']['MSC']['ow_key'] = &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['ow_key'];
            $GLOBALS['TL_LANG']['MSC']['ow_value'] = &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['ow_value'];
        }
    }


    public function validatePath( $strValue ) {

        if ( Toolkit::isEmpty( $strValue ) ) return '';

        if ( substr( $strValue, 0, 1 ) == '/' ) {

            $strValue = substr( $strValue, 1, strlen( $strValue ) );
        }

        if ( !is_dir( $strValue ) ) {

            throw new \Exception( 'Directory do not exist.' );
        }

        return $strValue;
    }


    public function setOrderField( \DataContainer $dc ) {
        
        if ( \Input::get( 'act' ) != 'edit' ) return;

        $objField = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE id = ?' )->limit(1)->execute( $dc->id );

        if ( $objField === null ) return;

        if ( $objField->numRows ) {

            $strOrder = $objField->sortBy;

            if ( $strOrder != 'custom' ) {

                unset( $GLOBALS['TL_DCA']['tl_catalog_fields']['fields']['orderField'] );

                $GLOBALS['TL_DCA']['tl_catalog_fields']['subpalettes']['fileType_files'] = str_replace( 'orderField,', '', $GLOBALS['TL_DCA']['tl_catalog_fields']['subpalettes']['fileType_files'] );
                $GLOBALS['TL_DCA']['tl_catalog_fields']['subpalettes']['fileType_gallery'] = str_replace( 'orderField,', '', $GLOBALS['TL_DCA']['tl_catalog_fields']['subpalettes']['fileType_gallery'] );
            }
        }
    }


    public function createFieldOnSubmit( \DataContainer $dc ) {

        $strID = $dc->activeRecord->pid;
        $objSQLBuilder = new SQLBuilder();
        $strIndex = $dc->activeRecord->useIndex;
        $strStatement = Toolkit::getSqlDataType( $dc->activeRecord->statement );
        $arrCatalog = $this->Database->prepare('SELECT * FROM tl_catalog WHERE id = ? LIMIT 1')->execute( $strID )->row();

        if ( in_array( $dc->activeRecord->fieldname , Toolkit::columnsBlacklist() ) ) {

            throw new \Exception( sprintf( 'Fieldname "%s" is not allowed.', $dc->activeRecord->fieldname ) );
        }

        if ( !$this->Database->fieldExists( $dc->activeRecord->fieldname, $arrCatalog['tablename'] ) ) {

            if ( in_array( $dc->activeRecord->type, Toolkit::excludeFromDc() ) ) {

                return null;
            }

            $objSQLBuilder->alterTableField( $arrCatalog['tablename'], $dc->activeRecord->fieldname, $strStatement );

            if ( $strIndex ) {

                $objSQLBuilder->addIndex( $arrCatalog['tablename'], $dc->activeRecord->fieldname, $strIndex );
            }
        }

        else {
            
            if ( in_array( $dc->activeRecord->type , Toolkit::excludeFromDc() ) ) {

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

        return [ 'image', 'gallery', 'file', 'files' ];
    }

    
    public function getTextFieldsByParentID() {

        $arrReturn = [ 'title' ];

        if ( !empty( $this->arrTextFieldsCache ) && is_array( $this->arrTextFieldsCache ) ) {

            return $this->arrTextFieldsCache;
        }

        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT pid FROM tl_catalog_fields WHERE id = ? )' )->execute( \Input::get('id') );

        while ( $objCatalogFields->next() ) {

            if ( $objCatalogFields->type !== 'text' ) {

                continue;
            }

            $arrReturn[] = $objCatalogFields->fieldname;
        }

        $this->arrTextFieldsCache = $arrReturn;

        return $this->arrTextFieldsCache;
    }

    
    public function getCatalogFieldsByParentID() {

        if ( !empty( $this->arrFieldsCache ) && is_array( $this->arrFieldsCache ) ) {

            return $this->arrFieldsCache;
        }

        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT pid FROM tl_catalog_fields WHERE id = ? )' )->execute( \Input::get('id') );

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->fieldname ) continue;

            $this->arrFieldsCache[] = $objCatalogFields->fieldname;
        }

        return $this->arrFieldsCache;
    }

    
    public function renameFieldname( $varValue, \DataContainer $dc ) {

        if ( !$varValue || !$dc->activeRecord->fieldname || $dc->activeRecord->fieldname == $varValue ) {

            return $varValue;
        }

        $strStatement = Toolkit::getSqlDataType( $dc->activeRecord->statement );
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


    public function checkBlacklist( $varValue ) {

        if ( $varValue && in_array( $varValue, Toolkit::columnsBlacklist() ) ) {

            throw new \Exception( sprintf( 'Fieldname "%s" is forbidden.', $varValue ) );
        }

        return $varValue;
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

        return [ 'text', 'date', 'radio', 'hidden', 'number', 'select', 'upload', 'message', 'checkbox', 'textarea', 'dbColumn', 'map', 'fieldsetStart', 'fieldsetStop' ];
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

        $arrReturn = [ 'tinyMCE', 'tinyFlash' ];

        if ( version_compare( VERSION, '4.0', '>=' ) ) {

            $arrCustomTinyMce = $this->getTemplateGroup( 'be_tiny' );

            if ( !empty( $arrCustomTinyMce ) && is_array( $arrCustomTinyMce ) ) {

                foreach ( $arrCustomTinyMce as $strTinyMCE => $strTinyMCEName ) {

                    $strTinyMCE = $strTinyMCE ? str_replace( 'be_', '', $strTinyMCE ) : '';

                    if ( !$strTinyMCE ) continue;

                    if ( !in_array( $strTinyMCE, $arrReturn ) ) {

                        $arrReturn[] = $strTinyMCE;
                    }
                }
            }

            return $arrReturn;
        }

        if ( !empty( $GLOBALS['TL_CATALOG_MANAGER']['tinyMCE'] ) && is_array( $GLOBALS['TL_CATALOG_MANAGER']['tinyMCE'] ) ) {

            return $GLOBALS['TL_CATALOG_MANAGER']['tinyMCE'];
        }

        return $arrReturn;
    }

    
    public function getTLClasses() {

        return [ 'w50', 'long', 'm12', 'clr' ];
    }

    
    public function getFieldFlags() {

        return [ '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12' ];
    }

    
    public function getSQLStatements( \DataContainer $dc ) {

        $arrSQLStatements = Toolkit::$arrSqlTypes;

        if ( $dc->activeRecord->type == 'upload' ) {

            return [

                'blob' => $arrSQLStatements['blob'],
                'binary' => $arrSQLStatements['binary']
            ];
        }

        if ( $dc->activeRecord->type == 'textarea' || $dc->activeRecord->multiple ) {

            unset( $arrSQLStatements['i5'] );
            unset( $arrSQLStatements['c1'] );
            unset( $arrSQLStatements['c16'] );
            unset( $arrSQLStatements['c32'] );
            unset( $arrSQLStatements['c64'] );
            unset( $arrSQLStatements['c128'] );
            unset( $arrSQLStatements['c256'] );
        }

        return $arrSQLStatements;
    }

    
    public function getCatalogFieldList( $arrRow ) {

        return $arrRow['title'] . ( $arrRow['fieldname'] ? ' ' . '<span style="color:#ccc;">[' . $arrRow['fieldname'] . ']</span>' : '' );
    }

    
    public function getMapTemplates() {

        return $this->getTemplateGroup( 'ctlg_field_' );
    }


    public function getTaxonomyTable( \DataContainer $dc ) {

        return $dc->activeRecord->dbTable ? $dc->activeRecord->dbTable : '';
    }


    public function getTaxonomyFields( \DataContainer $dc, $strTablename ) {

        $arrReturn = [];
        $arrForbiddenTypes = [ 'upload', 'textarea' ];

        if ( !$strTablename ) return $arrReturn;

        if ( Toolkit::isCoreTable( $strTablename ) ) {

            return Toolkit::getColumnsFromCoreTable( $strTablename, true );
        }

        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize( $strTablename );
        $arrFields = $objCatalogFieldBuilder->getCatalogFields( true, null );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !$this->Database->fieldExists( $strFieldname, $strTablename ) ) continue;
            if ( in_array( $arrField['type'], Toolkit::columnOnlyFields() ) ) continue;
            if ( in_array( $arrField['type'], Toolkit::excludeFromDc() ) ) continue;
            if ( in_array( $arrField['type'], $arrForbiddenTypes ) ) continue;

            $arrReturn[ $strFieldname ] = $arrField['_dcFormat'];
        }

        return $arrReturn;
    }


    public function getImageTemplates() {

        return $this->getTemplateGroup( 'ce_image' );
    }


    public function getGalleryTemplates() {

        return $this->getTemplateGroup( 'gallery_default' );
    }


    public function getFileTemplates() {

        return $this->getTemplateGroup( 'ce_download' );
    }


    public function getFilesTemplates() {

        return $this->getTemplateGroup( 'ce_downloads' );
    }


    public function getOrderFields( \DataContainer $dc ) {

        $arrReturn = [];

        if ( $dc->activeRecord ) {

            $objFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ? AND statement = ?' )->execute( $dc->activeRecord->pid, 'blob' );

            while ( $objFields->next() ) {

                if ( $objFields->fieldname && $objFields->type == 'dbColumn' ) {

                    $arrReturn[ $objFields->fieldname ] = $objFields->fieldname;
                }
            }
        }

        return $arrReturn;
    }
}