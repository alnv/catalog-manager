<?php

namespace CatalogManager;

class Hidden {

    public static function generate( $arrDCAField, $arrField ) {

        $arrDCAField['eval']['hideInput'] = true;

        if ( $arrField['tstampAsDefault'] ) {

            $arrDCAField['default'] = time();
        }

        return $arrDCAField;
    }
}