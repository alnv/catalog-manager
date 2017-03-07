<?php

namespace CatalogManager;

class tl_module extends \Backend {


    private $arrCatalogFieldsCache = [];
    private $arrSortableCatalogFieldsCache = [];


    public function __construct() {

        parent::__construct();

        $this->import( 'DCABuilderHelper' );
    }


    public function getCatalogs() {

        $arrReturn = [];

        if ( !empty( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] ) && is_array( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] ) ) {

            foreach ( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] as $arrCatalog ) {

                $arrReturn[ $arrCatalog['tablename'] ] = $arrCatalog['name'];
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

        return $this->getTemplateGroup('ctlg_view_');
    }


    public function getCatalogFormTemplates() {

        return $this->getTemplateGroup('ctlg_form_');
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

            if ( !$objCatalogFields->title ) {

                continue;
            }

            if ( !$objCatalogFields->optionsType || $objCatalogFields->optionsType == 'useOptions' ) {

                continue;
            }

            $arrReturn[$objCatalogFields->id] = $objCatalogFields->title;
        }

        return $arrReturn;
    }


    public function getChildTablesByTablename( \DataContainer $dc ) {

        $arrReturn = [];
        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][  $dc->activeRecord->catalogTablename ];

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

        if ( !empty( $this->arrSortableCatalogFieldsCache ) && is_array( $this->arrSortableCatalogFieldsCache ) ) {

            return $this->arrSortableCatalogFieldsCache;
        }

        $arrFields = [

            'id' => 'ID',
            'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['title'][0],
            'alias' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['alias'][0]
        ];

        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT id FROM tl_catalog WHERE tablename = ? LIMIT 1 ) ORDER BY sorting' )->execute( $strTablename );

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->type || in_array( $objCatalogFields->type, [ 'fieldsetStart', 'fieldsetStop', 'map', 'message', 'upload', 'textarea' ] ) ) {

                continue;
            }
            
            $arrFields[ $objCatalogFields->fieldname ] = $objCatalogFields->title ? $objCatalogFields->title : $objCatalogFields->fieldname;
        }

        if ( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strTablename] && is_array(  $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strTablename]['operations'] ) ) {

            if ( in_array( 'invisible', $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strTablename]['operations'] ) ) {

                $arrFields['stop'] = &$GLOBALS['TL_LANG']['catalog_manager']['fields']['stop'][0];
                $arrFields['start'] = &$GLOBALS['TL_LANG']['catalog_manager']['fields']['start'][0];
                $arrFields['invisible'] = &$GLOBALS['TL_LANG']['catalog_manager']['fields']['invisible'][0];
            }
        }

        $this->arrSortableCatalogFieldsCache = $arrFields;

        return $this->arrSortableCatalogFieldsCache;
    }


    public function getOrderByItems() {

        return [ 'ASC' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['asc'], 'DESC' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['desc'] ];
    }

    
    public function getTaxonomyTable( \DataContainer $dc ) {
        
        return $dc->activeRecord->catalogTablename ? $dc->activeRecord->catalogTablename : '';
    }

    
    public function getTaxonomyFields( \DataContainer $dc, $strTablename ) {

        $arrReturn = [];
        
        if ( !$strTablename ) return $arrReturn;

        $this->import( 'DCABuilderHelper' );
        $arrForbiddenTypes = [ 'upload', 'textarea' ];
        $arrReturn = $this->DCABuilderHelper->getPredefinedFields();
        $arrCatalog = &$GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTablename ];
        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ? ORDER BY sorting' )->execute( $arrCatalog['id'] );

        while ( $objCatalogFields->next() ) {

            if ( in_array( $objCatalogFields->type, $this->DCABuilderHelper->arrForbiddenInputTypes ) || in_array( $objCatalogFields->type, $arrForbiddenTypes ) ) {

                continue;
            }

            $arrReturn[ $objCatalogFields->fieldname ] = $objCatalogFields->row();
        }

        return $arrReturn;
    }


    public function getFilterFields( \DataContainer $dc ) {

        if ( !$dc->activeRecord->catalogTablename ) return [];

        $arrReturn = [];
        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT id FROM tl_catalog WHERE tablename = ? )' )->execute( $dc->activeRecord->catalogTablename );

        while ( $objCatalogFields->next() ) {

            if ( !$objCatalogFields->fieldname ) continue;
            if ( $objCatalogFields->type == 'upload' ) continue;
            if ( in_array( $objCatalogFields->type, $this->DCABuilderHelper->arrForbiddenInputTypes ) ) continue;

            $arrReturn[ $objCatalogFields->id ] = $objCatalogFields->title ? $objCatalogFields->title : $objCatalogFields->fieldname;
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
}
