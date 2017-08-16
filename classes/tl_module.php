<?php

namespace CatalogManager;

class tl_module extends \Backend {


    private $arrCatalogFieldsCache = [];


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


    public function getJoinAbleFields( \DataContainer $dc ) {

        $arrReturn = [];
        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $dc->activeRecord->catalogTablename ];

        if ( !$arrCatalog || empty( $arrCatalog ) || !is_array( $arrCatalog )  ) return $arrReturn;

        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ?' )->execute( $arrCatalog['id'] );

        while ( $objCatalogFields->next() ) {

            if ( !in_array( $objCatalogFields->type, [ 'select', 'checkbox', 'radio' ] ) ) {

                continue;
            }

            if ( !$objCatalogFields->optionsType || $objCatalogFields->optionsType == 'useOptions' ) {

                continue;
            }

            $arrReturn[ $objCatalogFields->fieldname ] = $objCatalogFields->title ? $objCatalogFields->title . ' [' . $objCatalogFields->fieldname . ']' : $objCatalogFields->fieldname;
        }

        return $arrReturn;
    }


    public function getChildTablesByTablename( \DataContainer $dc ) {

        $arrReturn = [];
        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $dc->activeRecord->catalogTablename ];

        if ( !$arrCatalog || empty( $arrCatalog ) || !is_array( $arrCatalog )  ) return $arrReturn;

        return ( is_array( $arrCatalog['cTables'] ) ? $arrCatalog['cTables'] : deserialize( $arrCatalog['cTables'] ) );
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

        $strTable = $dc->activeRecord->catalogTablename;

        if ( !empty( $this->arrCatalogFieldsCache ) && is_array( $this->arrCatalogFieldsCache ) ) {

            return $this->arrCatalogFieldsCache;
        }

        if ( $strTable && $this->Database->tableExists( $strTable ) ) {

            $arrColumns = $this->Database->listFields( $strTable );

            $this->arrCatalogFieldsCache = Toolkit::parseColumns( $arrColumns );
        }

        return $this->arrCatalogFieldsCache;
    }
    

    public function getSortableCatalogFieldsByTablename( $strTablename ) {

        $arrFields = [];
        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTablename ];
        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT id FROM tl_catalog WHERE tablename = ? LIMIT 1 ) ORDER BY sorting' )->execute( $strTablename );

        $objI18nCatalogTranslator = new I18nCatalogTranslator();
        $objI18nCatalogTranslator->initialize();

        if ( is_array( $arrCatalog ) ) {

            $arrOperations = $arrCatalog['operations'];

            $arrFields['id'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['id'][0];
            $arrFields['title'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['title'][0];
            $arrFields['alias'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['alias'][0];
            $arrFields['tstamp'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['tstamp'][0];

            if ( in_array( 'invisible', $arrOperations ) && $this->Database->fieldExists( 'invisible', $strTablename ) ) {

                $arrFields['stop'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['stop'][0];
                $arrFields['start'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['start'][0];
                $arrFields['invisible'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['invisible'][0];
            }

            if ( $this->Database->fieldExists( 'sorting', $strTablename ) ) {

                $arrFields['sorting'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['sorting'][0];
            }
        }

        if ( !$objCatalogFields->numRows ) return $arrFields;

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->fieldname || !$objCatalogFields->type ) {

                continue;
            }

            if ( in_array( $objCatalogFields->type, [ 'fieldsetStart', 'fieldsetStop', 'map', 'message', 'upload', 'textarea' ] ) ) {

                continue;
            }

            $arrLabels = $objI18nCatalogTranslator->getFieldLabel( $objCatalogFields->fieldname, $objCatalogFields->title, $objCatalogFields->description );
            $arrFields[ $objCatalogFields->fieldname ] = $arrLabels[0] ? $arrLabels[0] : $objCatalogFields->fieldname;
        }

        return $arrFields;
    }


    public function getOrderByItems() {

        return [ 'ASC' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['asc'], 'DESC' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['desc'] ];
    }

    
    public function getTaxonomyTable( \DataContainer $dc ) {

        $strTable = $dc->activeRecord->catalogTablename ? $dc->activeRecord->catalogTablename : '';

        if ( $dc->activeRecord->type == 'catalogTaxonomyTree' && $dc->activeRecord->catalogRoutingSource == 'page' && $dc->activeRecord->catalogPageRouting ) {

            $objPage = $this->Database->prepare( 'SELECT * FROM tl_page WHERE id = ?' )->limit(1)->execute( $dc->activeRecord->catalogPageRouting );

            if ( $objPage->numRows ) {

                $strTable = $objPage->catalogUseRouting ? $objPage->catalogRoutingTable : $strTable;
            }
        }

        return $strTable;
    }

    
    public function getTaxonomyFields( \DataContainer $dc, $strTablename, $arrForbiddenTypes = null ) {

        $arrReturn = [];

        if ( !$strTablename ) return $arrReturn;

        if ( is_null( $arrForbiddenTypes ) || !is_array( $arrForbiddenTypes ) ) {

            $arrForbiddenTypes = [ 'upload', 'textarea' ];
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

            if ( !in_array( $objCatalogFields->type, [ 'select', 'radio', 'checkbox' ] ) ) {

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

        if ( !$dc->activeRecord->catalogTablename ) return [];

        $arrReturn = [];
        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $dc->activeRecord->catalogTablename ];
        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT id FROM tl_catalog WHERE tablename = ? LIMIT 1 ) ORDER BY sorting' )->execute( $dc->activeRecord->catalogTablename );

        if ( is_array( $arrCatalog ) ) {

            $arrOperations = $arrCatalog['operations'];

            $arrReturn['title'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['title'][0] . ' [title]';
            $arrReturn['alias'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['alias'][0] . ' [alias]';

            if ( in_array( 'invisible', $arrOperations ) && $this->Database->fieldExists( 'invisible', $dc->activeRecord->catalogTablename ) ) {

                $arrReturn['stop'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['stop'][0] . ' [stop]';
                $arrReturn['start'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['start'][0] . ' [start]';
                $arrReturn['invisible'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['invisible'][0] . ' [invisible]';
            }
        }

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->type || !$objCatalogFields->fieldname ) {

                continue;
            }

            if ( in_array( $objCatalogFields->type, [ 'fieldsetStart', 'fieldsetStop', 'map', 'message', 'dbColumn' ] ) ) {

                continue;
            }

            $arrReturn[ $objCatalogFields->fieldname ] = $objCatalogFields->title ? $objCatalogFields->title . ' ['. $objCatalogFields->fieldname .']' : $objCatalogFields->fieldname;
        }

        return $arrReturn;
    }


    public function getAllColumns( \DataContainer $dc ) {

        $arrReturn = [];

        if ( !$dc->activeRecord->catalogTablename ) return $arrReturn;

        $arrColumns = $this->getTaxonomyFields( $dc, $dc->activeRecord->catalogTablename, [] );

        if ( !empty( $arrColumns ) && is_array( $arrColumns ) ) {

            foreach ( $arrColumns as $strFieldname => $arrField ) {

                $arrReturn[ $strFieldname ] = $arrField['title'] ? $arrField['title'] . ' ['. $arrField['fieldname'] .']' : $arrField['fieldname'];
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
}
