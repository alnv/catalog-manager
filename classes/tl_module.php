<?php

namespace CatalogManager;

class tl_module extends \Backend {

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

    public function getCatalogTemplates() {

        return $this->getTemplateGroup('catalog_');
    }

    public function getCatalogFormTemplates() {

        return $this->getTemplateGroup('form_catalog_');
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

    public function getChildTables( \DataContainer $dc ) {

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

        return $this->getTemplateGroup('catalog_map_');
    }
}
