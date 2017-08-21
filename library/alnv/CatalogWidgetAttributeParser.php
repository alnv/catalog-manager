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

                $arrValue[] = [

                    'key' => $strArea,
                    'value' => $GLOBALS['TL_LANG']['MOD'][ $strArea ]
                ];
            }

            $arrAttributes['value'] = $arrValue;
        }

        return $arrAttributes;
    }
}