<?php

namespace CatalogManager;

class tl_module extends \Backend {


    protected $arrFields = [];


    public function __construct() {

        parent::__construct();
    }


    public function checkModuleRequirements() {

        if ( !$this->Database->tableExists( 'tl_nc_notification' ) ) {

            unset( $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDelete']['relation'] );
            unset( $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDelete']['options_callback'] );

            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDelete']['options'] = [];
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDelete']['eval']['chosen'] = false;
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDelete']['eval']['disabled'] = true;
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDelete']['eval']['blankOptionLabel'] = 'notification center required';

            unset( $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyUpdate']['relation'] );
            unset( $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyUpdate']['options_callback'] );

            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyUpdate']['options'] = [];
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyUpdate']['eval']['chosen'] = false;
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyUpdate']['eval']['disabled'] = true;
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyUpdate']['eval']['blankOptionLabel'] = 'notification center required';

            unset( $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyInsert']['relation'] );
            unset( $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyInsert']['options_callback'] );

            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyInsert']['options'] = [];
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyInsert']['eval']['chosen'] = false;
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyInsert']['eval']['disabled'] = true;
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyInsert']['eval']['blankOptionLabel'] = 'notification center required';

            unset( $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDuplicate']['relation'] );
            unset( $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDuplicate']['options_callback'] );

            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDuplicate']['options'] = [];
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDuplicate']['eval']['chosen'] = false;
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDuplicate']['eval']['disabled'] = true;
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogNotifyDuplicate']['eval']['blankOptionLabel'] = 'notification center required';
        }
    }


    public function getCatalogs() {

        $arrReturn = [];

        if ( !empty( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] ) && is_array( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] ) ) {

            foreach ( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] as $arrCatalog ) {

                $arrReturn[ $arrCatalog['tablename'] ] = $arrCatalog['name'] . ( $arrCatalog['info'] ? ' (' . \StringUtil::substr( $arrCatalog['info'], 16 ) . ')' : '' );
            }
        }

        return $arrReturn;
    }


    public function disableNotRequiredFields( \DataContainer $dc ) {

        $arrModule = $this->Database->prepare('SELECT * FROM tl_module WHERE id = ?')->limit(1)->execute( $dc->id )->row();
        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $arrModule['catalogTablename'] ];

        if ( !$arrCatalog ) return null;

        if ( !$arrCatalog['pTable'] ) {

            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogJoinParentTable']['eval']['disabled'] = true;
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogRelatedParentTable']['eval']['chosen'] = false;
            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogRelatedParentTable']['eval']['disabled'] = true;
        }

        if ( empty( $arrCatalog['cTables'] ) ) {

            $GLOBALS['TL_DCA']['tl_module']['fields']['catalogRelatedChildTables']['eval']['disabled'] = true;
        }

        if ( $arrModule['type'] == 'catalogFilter' ) {

            \Message::addError( 'This module is deprecated. Please use filter generator.' );
        }
    }


    public function generateGeoCords( \DataContainer $dc ) {

        if ( !$dc->activeRecord->catalogMapAddress ) return null;

        $arrSet = [];
        $objGeoCoding = new GeoCoding();
        $arrCords = $objGeoCoding->getCords( $dc->activeRecord->catalogMapAddress, 'en', true );

        if ( ( $arrCords['lat'] || $arrCords['lng'] ) ) {

            $arrSet['catalogMapLng'] = $arrCords['lng'];
            $arrSet['catalogMapLat'] = $arrCords['lat'];

            $this->Database->prepare( 'UPDATE '. $dc->table .' %s WHERE id = ?' )->set( $arrSet )->execute( $dc->id );
        }
    }


    public function getSystemCountries() {

        return array_values( $this->getcountries() );
    }


    public function getCatalogTemplates() {

        $arrReturn = [];
        $arrTemplates = $this->getTemplateGroup('ctlg_view_');
        $strNotAllowedTemplateName = 'ctlg_view_table';

        foreach ( $arrTemplates as $strTemplate => $strTemplateName ) {

            if ( strpos( $strTemplateName, $strNotAllowedTemplateName ) !== false ) {

                continue;
            }

            $arrReturn[ $strTemplate ] = $strTemplateName;
        }
        
        return $arrReturn;
    }


    public function getCatalogFormTemplates() {

        $arrReturn = [];
        $arrTypes = [ 'default', 'grouped' ];

        foreach ( $arrTypes as $strType ) {

            $arrTemplateGroup = $this->getTemplateGroup( 'ctlg_form_' . $strType );

            foreach ( $arrTemplateGroup as $strKey => $strValue ) {

                $arrReturn[ $strKey ] = $strValue;
            }
        }

        return $arrReturn;
    }


    public function getTableViewTemplates() {

        return $this->getTemplateGroup('mod_catalog_table');
    }


    public function getTableBodyViewTemplates() {

        return $this->getTemplateGroup('ctlg_view_table');
    }


    public function getCatalogOperationItems() {

        return [ 'create', 'copy', 'edit', 'delete' ];
    }


    public function getPdfTemplates() {

        return $this->getTemplateGroup('ctlg_pdf_');
    }


    public function getCatalogDownloads() {

        return [ 'pdf' ];
    }


    public function getJoinAbleFields( \DataContainer $dc ) {

        $arrReturn = [];
        $strTablename = $dc->activeRecord->catalogTablename;

        if ( Toolkit::isEmpty( $strTablename ) ) return $arrReturn;
        if ( !$this->Database->tableExists( $strTablename ) ) return $arrReturn;

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize( $strTablename );
        $arrFields = $objFieldBuilder->getCatalogFields( true, null );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !in_array( $arrField['type'], [ 'select', 'checkbox', 'radio' ] ) ) continue;
            if ( !$arrField['optionsType'] || $arrField['optionsType'] == 'useOptions' ) continue;

            $arrReturn[ $strFieldname ] = Toolkit::getLabelValue( $arrField['_dcFormat']['label'], $strFieldname );
        }

        return $arrReturn;
    }


    public function getChildTablesByTablename( \DataContainer $dc ) {

        $arrReturn = [];
        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $dc->activeRecord->catalogTablename ];

        if ( is_array( $arrCatalog ) && isset( $arrCatalog['cTables'] ) ) {

            $arrTables = Toolkit::deserialize( $arrCatalog['cTables'] );

            foreach ( $arrTables as $strTable ) {

                if ( is_array( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ] ) ) {

                    $strName = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ]['name'];

                    $arrReturn[ $strTable ] = $strName ? $strName : $strTable;
                }
            }
        }

        return $arrReturn;
    }


    public function getParentTable( \DataContainer $dc ) {

        $arrReturn = [];
        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $dc->activeRecord->catalogTablename ];

        if ( !$arrCatalog || empty( $arrCatalog ) || !is_array( $arrCatalog )  ) return $arrReturn;

        return [ ( $arrCatalog['pTable'] ? $arrCatalog['pTable'] : '' ) ];
    }


    public function getMapTemplates() {

        return $this->getTemplateGroup('ctlg_map_');
    }


    public function getMapViewTemplates(){

        return $this->getTemplateGroup('mod_catalog_map_');
    }


    public function getCatalogFieldsByTablename( \DataContainer $dc ) {

        $strTablename = $dc->activeRecord->catalogTablename;

        if ( Toolkit::isEmpty( $strTablename ) ) return [];
        if ( !$this->Database->tableExists( $strTablename ) ) return [];
        if ( isset( $this->arrFields[ $strTablename ] ) ) return $this->arrFields[ $strTablename ];

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize( $strTablename );
        $arrFields = $objFieldBuilder->getCatalogFields( true, null );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !Toolkit::isDcConformField( $arrField ) ) continue;

            $this->arrFields[ $strTablename ][ $strFieldname ] = Toolkit::getLabelValue( $arrField['_dcFormat']['label'], $strFieldname );
        }

        return $this->arrFields[ $strTablename ];
    }

    
    public function getTaxonomyTable( \DataContainer $dc ) {

        $strTable = $dc->activeRecord->catalogTablename ? $dc->activeRecord->catalogTablename : '';

        if ( $dc->activeRecord->type == 'catalogTaxonomyTree' && $dc->activeRecord->catalogRoutingSource == 'page' && $dc->activeRecord->catalogPageRouting ) {

            $objPage = $this->Database->prepare( 'SELECT * FROM tl_page WHERE id = ?' )->limit(1)->execute( $dc->activeRecord->catalogPageRouting );

            if ( $objPage->numRows ) $strTable = $objPage->catalogUseRouting ? $objPage->catalogRoutingTable : $strTable;
        }

        if ( !$this->Database->tableExists( $strTable ) ) return '';

        return $strTable;
    }

    
    public function getTaxonomyFields( \DataContainer $dc, $strTablename, $arrForbiddenTypes = null ) {

        $arrReturn = [];

        if ( !$strTablename ) return $arrReturn;
        if ( !$this->Database->tableExists( $strTablename ) ) return $arrReturn;

        if ( is_null( $arrForbiddenTypes ) || !is_array( $arrForbiddenTypes ) ) {

            $arrForbiddenTypes = [ 'upload' ];
        }

        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize( $strTablename );
        $arrFields = $objCatalogFieldBuilder->getCatalogFields( true, null );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !$this->Database->fieldExists( $strFieldname, $strTablename ) ) continue;
            if ( in_array( $arrField['type'], Toolkit::excludeFromDc() ) ) continue;
            if ( $arrField['type'] == 'textarea' && $arrField['rte'] ) continue;
            if ( in_array( $arrField['type'], $arrForbiddenTypes ) ) continue;

            $arrReturn[ $strFieldname ] = $arrField['_dcFormat'];
        }

        return $arrReturn;
    }


    public function getFilterFields( \DataContainer $dc ) {

        if ( !$dc->activeRecord->catalogTablename ) return [];

        $arrReturn = [];
        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT id FROM tl_catalog WHERE tablename = ? )' )->execute( $dc->activeRecord->catalogTablename );

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->fieldname ) continue;
            if ( in_array( $objCatalogFields->type, [ 'upload', 'dbColumn' ] ) ) continue;
            if ( in_array( $objCatalogFields->type, Toolkit::excludeFromDc() ) ) continue;

            $arrReturn[ $objCatalogFields->id ] = $objCatalogFields->title ? $objCatalogFields->title . ' <span style="color:#333; font-size:12px; display:inline">[ ' . $objCatalogFields->fieldname . ' ]</span>': $objCatalogFields->fieldname;
        }

        return $arrReturn;
    }


    public function getRoutingFields( \DataContainer $dc ) {

        if ( !$dc->activeRecord->catalogTablename ) return [];

        $arrReturn = [];
        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT id FROM tl_catalog WHERE tablename = ? )' )->execute( $dc->activeRecord->catalogTablename );

        if ( !$objCatalogFields->numRows ) return $arrReturn;

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->fieldname ) continue;

            if ( !in_array( $objCatalogFields->type, [ 'select', 'radio', 'checkbox', 'text', 'dbColumn', 'number' ] ) ) {

                continue;
            }

            $arrReturn[ $objCatalogFields->fieldname ] = $objCatalogFields->title ? $objCatalogFields->title . ' <span style="color:#333; font-size:12px; display:inline">[ ' . $objCatalogFields->fieldname . ' ]</span>' : $objCatalogFields->fieldname;
        }

        return $arrReturn;
    }


    public function getPageRouting( \DataContainer $dc ) {

        $arrReturn = [];
        $objPages = $this->Database->prepare( 'SELECT * FROM tl_page WHERE pid != ? AND catalogUseRouting = ?' )->execute( '0', '1' );

        if ( !$objPages->numRows ) return $arrReturn;

        while ( $objPages->next() ) {

            if ( $objPages->catalogRouting ) {

                $arrReturn[ $objPages->id ] = $objPages->title . ' <span style="color:#333; font-size:12px; display:inline">[' . $objPages->catalogRouting . ']</span>';
            }
        }

        return $arrReturn;
    }


    public function getActiveFilterFields( \DataContainer $dc ) {

        if ( !$dc->activeRecord->catalogActiveFilterFields ) return [];

        $arrReturn = [];
        $arrActiveFilterFields = Toolkit::deserialize( $dc->activeRecord->catalogActiveFilterFields );
        
        if ( empty( $arrActiveFilterFields ) || !is_array( $arrActiveFilterFields ) ) {

            return $arrReturn;
        }

        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE id IN ( '. implode( ',' , $arrActiveFilterFields ) .' )' )->execute();

        while ( $objCatalogFields->next() ) {

            $arrReturn[ $objCatalogFields->fieldname ] = $objCatalogFields->title ? $objCatalogFields->title : $objCatalogFields->fieldname;
        }

        return $arrReturn;
    }


    public function getExcludedCatalogFields( \DataContainer $dc ) {

        $arrReturn = [];
        $strTablename = $dc->activeRecord->catalogTablename;

        if ( !$strTablename ) return $arrReturn;
        if ( !$this->Database->tableExists( $strTablename ) ) return $arrReturn;

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize( $strTablename );
        $arrFields = $objFieldBuilder->getCatalogFields( true, null );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !$this->Database->fieldExists( $strFieldname, $strTablename ) ) continue;
            if ( in_array( $arrField['type'], Toolkit::columnOnlyFields() ) ) continue;
            if ( in_array( $arrField['type'], Toolkit::readOnlyFields() ) ) continue;
            if ( in_array( $arrField['type'], Toolkit::excludeFromDc() ) ) continue;

            $arrReturn[ $strFieldname ] = Toolkit::getLabelValue( $arrField['_dcFormat']['label'], $strFieldname );
        }

        return $arrReturn;
    }


    public function getFastModeFields( \DataContainer $dc ) {

        $arrReturn = [];
        $strTablename = $dc->activeRecord->catalogTablename;

        if ( !$strTablename ) return $arrReturn;
        if ( !$this->Database->tableExists( $strTablename ) ) return $arrReturn;

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize( $strTablename );
        $arrFields = $objFieldBuilder->getCatalogFields( true, null );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !in_array( $arrField['type'], Toolkit::$arrDoNotRenderInFastMode ) ) continue;

            $arrReturn[ $strFieldname ] = Toolkit::getLabelValue( $arrField['_dcFormat']['label'], $strFieldname );
        }

        return $arrReturn;
    }


    public function getAllColumns( \DataContainer $dc ) {

        $arrReturn = [];

        if ( !$dc->activeRecord->catalogTablename ) return $arrReturn;

        $arrColumns = $this->getTaxonomyFields( $dc, $dc->activeRecord->catalogTablename, [] );

        if ( !empty( $arrColumns ) && is_array( $arrColumns ) ) {

            foreach ( $arrColumns as $strFieldname => $arrField ) {

                $arrReturn[ $strFieldname ] = Toolkit::getLabelValue( $arrField['label'], $strFieldname ) . '['. $strFieldname .']';
            }
        }

        return $arrReturn;
    }


    public function getNotificationChoices( \DataContainer $dc ) {

        $strWhere = '';
        $arrValues = [];
        $arrChoices = [];

        if ( !$this->Database->tableExists( 'tl_nc_notification' ) ) return [];

        $arrTypes = $GLOBALS['TL_DCA']['tl_module']['fields'][ $dc->field ]['eval']['ncNotificationChoices'];

        if ( !empty( $arrTypes ) && is_array( $arrTypes ) ) {

            $strWhere = ' WHERE ' . implode( ' OR ', array_fill(0, count($arrTypes), 'type=?' ) );
            $arrValues = $arrTypes;
        }

        $objNotifications = $this->Database->prepare( 'SELECT id,title FROM tl_nc_notification' . $strWhere . ' ORDER BY title' )->execute( $arrValues );

        while ($objNotifications->next()) {

            $arrChoices[ $objNotifications->id ] = $objNotifications->title;
        }

        return $arrChoices;
    }


    public function getCustomTemplate( \DataContainer $dc ) {

        $arrTemplates = [

            'catalogFilter' => 'mod_catalog_filter',
            'catalogMasterView' => 'mod_catalog_master',
            'catalogTaxonomyTree' => 'mod_catalog_taxonomy',
            'catalogUniversalView' => 'mod_catalog_universal'
        ];

        if ( $dc->activeRecord->type ) {

            $strTemplate = $arrTemplates[ $dc->activeRecord->type ];

            if ( $strTemplate ) {

                return $this->getTemplateGroup( $strTemplate );
            }
        }

        return [];
    }


    public function getTaxonomyNavTemplate() {

        return $this->getTemplateGroup( 'ctlg_taxonomy_nav' );
    }


    public function getKeyColumns( $objWidget ) {

        $arrReturn = [];
        $strTablename = '';
        $objModule = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE id = ?', $objWidget->strTable ) )->limit(1)->execute( $objWidget->currentRecord );

        if ( $objModule->numRows ) if ( $objModule->catalogTablename ) $strTablename = $objModule->catalogTablename;

        if ( !$strTablename ) return $arrReturn;
        if ( !$this->Database->tableExists( $strTablename ) ) return $arrReturn;

        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize( $strTablename );
        $arrFields = $objCatalogFieldBuilder->getCatalogFields( true, null, false, false );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( in_array( $arrField['type'], [ 'fieldsetStart', 'fieldsetStop', 'map' ] ) ) {

                continue;
            }

            $arrReturn[ $strFieldname ] = Toolkit::getLabelValue( $arrField['_dcFormat']['label'], $strFieldname );
        }

        return $arrReturn;
    }


    public function getSocialSharingButtons() {

        return Toolkit::$arrSocialSharingButtons;
    }


    public function getSocialSharingTemplates() {

        return $this->getTemplateGroup('ce_social_sharing_buttons');
    }


    public function getArrayOptions() {

        return Toolkit::$arrArrayOptions;
    }


    public function checkSortingField( $strValue, \DataContainer $dc ) {

        if ( $strValue == 'manuel' ) {

            if ( !$this->Database->fieldExists( 'sorting', $dc->activeRecord->catalogTablename ) ) {

                throw new \Exception( 'This option requires sorting field.' );
            }

            return $strValue;
        }

        return $strValue;
    }
}
