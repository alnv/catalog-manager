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

    public function getCatalogTemplates() {

        return $this->getTemplateGroup('catalog_');
    }

    public function getJoinAbleFields( \DataContainer $dc ) {

        $arrReturn = [];
        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $dc->activeRecord->catalogTablename ];

        if ( !$arrCatalog || empty( $arrCatalog ) || !is_array( $arrCatalog )  ) return $arrReturn;

        $strID = $arrCatalog['id'];
        $objCatalogFields = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ?' )->execute( $strID );

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
}
