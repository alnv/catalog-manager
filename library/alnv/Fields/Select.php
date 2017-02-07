<?php

namespace CatalogManager;

class Select {

    public static function generate( $arrDCAField, $arrField, $arrCatalog = [] ) {

        $arrDCAField['eval']['chosen'] =  Toolkit::getBooleanByValue( $arrField['chosen'] );
        $arrDCAField['eval']['disabled'] = Toolkit::getBooleanByValue( $arrField['disabled'] );
        $arrDCAField['eval']['multiple'] =  Toolkit::getBooleanByValue( $arrField['multiple'] );
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

        if ( $arrDCAField['eval']['multiple'] ) {

            $arrDCAField['eval']['csv'] = ',';
        }
        
        return $arrDCAField;
    }
}