<?php

namespace CatalogManager;

class Radio {

    
    public static function generate( $arrDCAField, $arrField ) {

        $arrDCAField['eval']['disabled'] = Toolkit::getBooleanByValue( $arrField['disabled'] );
        $arrDCAField['eval']['includeBlankOption'] =  Toolkit::getBooleanByValue( $arrField['includeBlankOption'] );

        if ( $arrField['blankOptionLabel'] && is_string( $arrField['blankOptionLabel'] ) ) {

            $arrDCAField['eval']['blankOptionLabel'] = $arrField['blankOptionLabel'];
        }

        $objOptionGetter = new OptionsGetter( $arrField );

        if ( $objOptionGetter->isForeignKey() ) {

            $strForeignKey = $objOptionGetter->getForeignKey();

            if ( $strForeignKey ) {

                $arrDCAField['foreignKey'] = $strForeignKey;
            }
        }

        else {

            $arrDCAField['options'] = $objOptionGetter->getOptions();
        }

        return $arrDCAField;
    }
}