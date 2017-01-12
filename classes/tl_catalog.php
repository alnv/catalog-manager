<?php

namespace CatalogManager;

class tl_catalog extends \Backend {

    public function onSubmit( \DataContainer $dc ) {

        if ( !$dc->id || !$dc->activeRecord->tablename ) {

            return null;
        }

        if ( !$this->Database->tableExists( $dc->activeRecord->tablename ) ) {

            // create new table
        }
    }

    public function onDelete() {

        // delete table
    }

    public function getModeTypes () {

        return [ '0', '1', '2', '3', '4', '5' ];
    }

    public function getFlagTypes() {

        return [ '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12' ];
    }

    public function getDataContainerFields() {

        return [];
    }

    public function getParentFields() {

        return []; // get fields from CatalogManager
    }

    public function getNavigationAreas() {

        $arrModules = $GLOBALS['BE_MOD'] ? $GLOBALS['BE_MOD'] : [];
        $arrReturn = [];

        if ( !is_array( $arrModules ) ) {

            return [];
        }

        foreach ( $arrModules as $strName => $arrModule ) {

            $arrLabel = $GLOBALS['TL_LANG']['MOD'][ $strName ];
            $strModuleName = $strName;

            if ( $arrLabel && is_array( $arrLabel ) ) {

                $strModuleName = $arrLabel[0];
            }

            if ( is_string( $arrLabel ) ) {

                $strModuleName = $arrLabel;
            }

            $arrReturn[ $strName ] = $strModuleName;
        }

        return $arrReturn;
    }

    public function getNavigationPosition() {

        return [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20 ];
    }
}