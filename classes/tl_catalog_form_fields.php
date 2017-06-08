<?php

namespace CatalogManager;

class tl_catalog_form_fields extends \Backend {


    protected $strTablename;
    protected $arrFields = [];


    public function __construct() {

        parent::__construct();

        $this->strTablename = $this->getTablename();
        $this->arrFields = $this->getTableColumnsByTablename( $this->strTablename );
    }


    public function checkPermission() {

        $objDCAPermission = new DCAPermission();
        $objDCAPermission->checkPermissionByParent( 'tl_catalog_form_fields' , 'tl_catalog_form', 'filterform', 'filterformp' );
    }


    public function getFilterFormFields() {

        return [

            'text',
            'select',
            'checkbox',
            'radio',
            'range'
        ];
    }


    public function setBackendRow( $arrRow ) {

        return $arrRow['title'];
    }


    public function getTableColumns() {

        if ( empty( $this->arrFields ) || !is_array( $this->arrFields ) ) return [];

        return $this->arrFields;
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


    public function getTaxonomyTable( \DataContainer $dc ) {

        return $dc->activeRecord->dbTable ? $dc->activeRecord->dbTable : '';
    }


    public function getTaxonomyFields( \DataContainer $dc, $strTablename ) {

       return $this->getTableColumnsByTablename( $strTablename, [ 'upload', 'textarea' ], true );
    }


    protected function getTablename() {

        $objForm = $this->Database->prepare( 'SELECT * FROM tl_catalog_form WHERE id = (SELECT pid FROM tl_catalog_form_fields WHERE id = ? LIMIT 1)' )->limit(1)->execute( \Input::get('id') );

        if ( $objForm->numRows ) {

            return $objForm->catalog;
        }

        return null;
    }


    protected function getTableColumnsByTablename( $strTable, $arrForbiddenTypes = [], $blnFullContext = false ) {

        $arrReturn = [];

        if ( !$strTable ) return $arrReturn;

        $this->import( 'DCABuilderHelper' );
        $arrPredefinedFields = $this->DCABuilderHelper->getPredefinedFields();
        $arrCatalog = &$GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ];

        if ( !$arrCatalog || !is_array( $arrCatalog ) ) return $arrReturn;

        if ( !empty( $arrPredefinedFields ) && is_array( $arrPredefinedFields ) ) {

            foreach ( $arrPredefinedFields as $arrField ) {

                $strTitle = $arrField['title'] ? $arrField['title'] : $arrField['fieldname'];
                $varContext = $blnFullContext ? $arrField : $strTitle;
                $arrReturn[ $arrField['fieldname'] ] = $varContext;
            }
        }

        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ? ORDER BY sorting' )->execute( $arrCatalog['id'] );

        foreach ( $arrReturn as $strFieldname => $arrField ) {

            if ( !$this->Database->fieldExists( $strFieldname, $strTable ) ) {

                unset( $arrReturn[ $strFieldname ] );
            }
        }

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->fieldname ) {

                continue;
            }

            if ( in_array( $objCatalogFields->type, $arrForbiddenTypes ) ) {

                continue;
            }

            $strTitle = $objCatalogFields->title ? $objCatalogFields->title : $objCatalogFields->fieldname;
            $varContext = $blnFullContext ? $objCatalogFields->row() : $strTitle;
            $arrReturn[ $objCatalogFields->fieldname ] = $varContext;
        }

        return $arrReturn;
    }


    public function getFieldTemplates( \DataContainer $dc ) {

        $strType = $dc->activeRecord->type ? $dc->activeRecord->type : '';

        return $this->getTemplateGroup( sprintf( 'ctlg_form_field_%s', $strType ) );
    }
}