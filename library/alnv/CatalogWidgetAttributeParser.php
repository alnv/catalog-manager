<?php

namespace CatalogManager;

class CatalogWidgetAttributeParser extends CatalogController {

    public function parseCatalogNavigationAreasWidget( $arrAttributes ) {

        if ( TL_MODE != 'BE' || \Input::get('do') != 'settings' || !is_array( $GLOBALS['BE_MOD'] ) ) {
            return $arrAttributes;
        }

        if ( $arrAttributes['name'] == 'catalogNavigationAreas' ) {
            $arrValue = [];
            $arrCoreAreas = array_keys( $GLOBALS['BE_MOD'] );
            foreach ( $arrCoreAreas as $strArea ) {
                $strNavigationTitle = $GLOBALS['TL_LANG']['MOD'][ $strArea ];
                if ( is_array( $strNavigationTitle ) ) {
                    if ( isset ( $strNavigationTitle[0] ) ) {
                        $strNavigationTitle = $strNavigationTitle[0] ?: '-';
                    }
                    else {
                        $strNavigationTitle = '-';
                    }
                }
                $arrValue[] = [
                    'key' => $strArea,
                    'value' => $strNavigationTitle
                ];
            }
            $arrAttributes['value'] = $arrValue;
        }

        return $arrAttributes;
    }
}