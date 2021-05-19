<?php

namespace CatalogManager;

class tl_catalog_form_fields extends \Backend {


    protected $strTablename;
    protected $arrFields = [];


    public function __construct() {

        parent::__construct();
        
        $this->strTablename = $this->getTablename();
    }


    public function checkPermission() {

        $objDcPermission = new DcPermission();
        $objDcPermission->checkPermissionByParent( 'tl_catalog_form_fields' , 'tl_catalog_form', 'filterform', 'filterformp' );
    }


    public function getFilterFormFields() {

        return [

            'text',
            'select',
            'checkbox',
            'radio',
            'range',
            'hidden'
        ];
    }


    public function setBackendRow( $arrRow ) {

        return $arrRow["title"] . '<span style="color: #cccccc;"> ['. $arrRow["name"] .']</span>';
    }


    public function getTableColumns() {

        $arrReturn = [];
        $arrFields = $this->getTableColumnsByTablename( $this->strTablename, [ 'upload' ], true );

        if ( empty( $arrFields ) || !is_array( $arrFields ) ) return $arrReturn;

        foreach ( $arrFields as $strFieldname => $arrField ) {

            $arrReturn[ $strFieldname ] = $arrField['label'][0] ? $arrField['label'][0] : $strFieldname;
        }

        return $arrReturn;
    }


    public function getTables() {

        return $this->Database->listTables( null );
    }


    public function getColumnsByDbTable( \DataContainer $dc ) {

        $arrReturn = [];
        $strTablename = $dc->activeRecord->dbTable;

        if ( $strTablename && $this->Database->tableExists( $strTablename ) ) {

            $objCatalogFieldBuilder = new CatalogFieldBuilder();
            $objCatalogFieldBuilder->initialize( $strTablename );
            $arrFields = $objCatalogFieldBuilder->getCatalogFields( true, null );

            foreach ( $arrFields as $strFieldname => $arrField ) {

                if ( !$this->Database->fieldExists( $strFieldname, $strTablename ) ) continue;

                $arrReturn[ $strFieldname ] = Toolkit::getLabelValue( $arrField['_dcFormat']['label'], $strFieldname );
            }
        }

        return $arrReturn;
    }


    public function getTaxonomyTable( \DataContainer $dc ) {

        return $dc->activeRecord->dbTable ? $dc->activeRecord->dbTable : '';
    }


    public function getTaxonomyFields(\DataContainer $dc, $strTablename) {

        return $this->getTableColumnsByTablename($strTablename, ['upload'], true);
    }


    protected function getTablename() {

        $objForm = $this->Database->prepare( 'SELECT * FROM tl_catalog_form_fields WHERE id = ?' )->limit(1)->execute( \Input::get('id') );

        if ( $objForm->numRows ) {

            return $objForm->dbTable;
        }

        return null;
    }


    protected function getTableColumnsByTablename( $strTablename, $arrForbiddenTypes = [], $blnFullContext = false ) {

        $arrReturn = [];

        if ( !$strTablename ) return $arrReturn;

        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize( $strTablename );
        $arrFields = $objCatalogFieldBuilder->getCatalogFields( true, null );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( in_array( $arrField['type'], Toolkit::excludeFromDc() ) ) continue;
            if ( in_array( $arrField['type'], $arrForbiddenTypes ) ) continue;

            $strTitle = $arrField['title'] ? $arrField['title'] : $strFieldname;
            $varValue = $blnFullContext ? $arrField['_dcFormat'] : $strTitle;
            $arrReturn[ $strFieldname ] = $varValue;
        }

        return $arrReturn;
    }


    public function getFieldTemplates( \DataContainer $dc ) {

        $strType = $dc->activeRecord->type ? $dc->activeRecord->type : '';

        return $this->getTemplateGroup( sprintf( 'ctlg_form_field_%s', $strType ) );
    }


    public function getFormFields( \DataContainer $dc ) {

        $arrReturn = [];
        $objFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_form_fields WHERE pid = ? AND id != ? ORDER BY sorting' )->execute( $dc->activeRecord->pid, $dc->activeRecord->id );

        if ( !$objFields->numRows ) return $arrReturn;

        while ( $objFields->next() ) {

            $arrReturn[ $objFields->name ] = $objFields->title ? $objFields->title : $objFields->name;
        }

        return $arrReturn;
    }


    public function getRGXPTypes() {

        return [ 'url', 'time', 'date', 'alias', 'alnum', 'alpha', 'datim', 'digit', 'email', 'extnd', 'phone', 'prcnt', 'locale', 'emails', 'natural', 'friendly', 'language', 'folderalias' ];
    }
}