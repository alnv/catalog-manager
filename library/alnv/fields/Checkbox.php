<?php

namespace CatalogManager;

class Checkbox {

    public static function generate( $arrDCAField, $arrField ) {

        $arrDCAField['eval']['disabled'] = Toolkit::getBooleanByValue( $arrField['disabled'] );
        $arrDCAField['eval']['multiple'] =  Toolkit::getBooleanByValue( $arrField['multiple'] );

        // $arrDCAField['options'] = [];

        return $arrDCAField;
    }
}