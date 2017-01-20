<?php

namespace CatalogManager;

class Select {

    public static function generate( $arrDCAField, $arrField ) {

        $arrDCAField['eval']['chosen'] =  Toolkit::getBooleanByValue( $arrField['chosen'] );
        $arrDCAField['eval']['disabled'] = Toolkit::getBooleanByValue( $arrField['disabled'] );
        $arrDCAField['eval']['multiple'] =  Toolkit::getBooleanByValue( $arrField['multiple'] );
        $arrDCAField['eval']['includeBlankOption'] =  Toolkit::getBooleanByValue( $arrField['includeBlankOption'] );

        if ( $arrField['blankOptionLabel'] && is_string( $arrField['blankOptionLabel'] ) ) {

            $arrDCAField['eval']['blankOptionLabel'] = $arrField['blankOptionLabel'];
        }

        $arrDCAField['options'] = [];

        return $arrDCAField;
    }
}