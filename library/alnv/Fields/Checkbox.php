<?php

namespace CatalogManager;

class Checkbox {

    
    public static function generate( $arrDCAField, $arrField ) {

        $arrDCAField['eval']['disabled'] = Toolkit::getBooleanByValue( $arrField['disabled'] );
        $arrDCAField['eval']['multiple'] =  Toolkit::getBooleanByValue( $arrField['multiple'] );

        $objOptionGetter = new OptionsGetter( $arrField );

        if ( $objOptionGetter->isForeignKey() ) {

            $strForeignKey = $objOptionGetter->getForeignKey();

            if ( $strForeignKey ) {

                $arrDCAField['foreignKey'] = $strForeignKey;
            }
        }

        else {

            $arrOptions = $objOptionGetter->getOptions();

            if ( !empty( $arrOptions ) ) {

                $arrDCAField['options'] = $arrOptions;
            }
        }

        $arrDCAField['eval']['csv'] = ',';

        return $arrDCAField;
    }
}